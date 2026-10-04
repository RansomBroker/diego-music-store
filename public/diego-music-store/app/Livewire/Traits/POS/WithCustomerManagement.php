<?php

namespace App\Livewire\Traits\POS;

use App\Models\Customer;
use App\Actions\Customer\CreateCustomer;
use Filament\Notifications\Notification;

trait WithCustomerManagement
{
    public function selectCustomer($id, $name, $isLoyalty)
    {
        $this->selectedCustomerId = $id;
        $this->selectedCustomerName = $name;
        $this->isLoyaltyMember = $isLoyalty;
        $this->customerSearch = ''; // Clear search
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->usePoints = false;

        // Automatically set pricing tier if registered for this customer
        $customer = Customer::find($id);
        if ($customer) {
            $this->customerPoints = $customer->loyalty_points;
            $this->customerPhone = $customer->phone ?? '';
            if ($customer->pricing_tier_id) {
                $this->selectedPricingTierId = $customer->pricing_tier_id;
            } else {
                // Fallback to default retail tier
                $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
                    ->orWhere('name', 'like', '%umum%')
                    ->first() ?? \App\Models\PricingTier::first();
                $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;
            }
        } else {
            $this->customerPoints = 0;
            // Fallback to default retail tier
            $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
                ->orWhere('name', 'like', '%umum%')
                ->first() ?? \App\Models\PricingTier::first();
            $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;
        }
        $this->updatedSelectedPricingTierId($this->selectedPricingTierId);
    }

    public function clearCustomer()
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = 'Umum / Walk-in';
        $this->customerPhone = '';
        $this->isLoyaltyMember = false;
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->customerPoints = 0;
        $this->usePoints = false;

        // Fallback to default retail tier
        $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
            ->orWhere('name', 'like', '%umum%')
            ->first() ?? \App\Models\PricingTier::first();
        $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;
        $this->updatedSelectedPricingTierId($this->selectedPricingTierId);
    }

    public function openCreateCustomerModal()
    {
        $this->newCustomerName = $this->customerSearch;
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerAddress = '';
        
        $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
            ->orWhere('name', 'like', '%umum%')
            ->first() ?? \App\Models\PricingTier::first();
        $this->newCustomerPricingTierId = $defaultTier ? $defaultTier->id : null;
        $this->newCustomerIsLoyaltyMember = true;
        
        $this->showCreateCustomerModal = true;
    }

    public function createCustomer(CreateCustomer $createCustomerAction)
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'nullable|string|max:255',
            'newCustomerEmail' => 'nullable|email|max:255',
            'newCustomerAddress' => 'nullable|string|max:1000',
            'newCustomerPricingTierId' => 'nullable|exists:pricing_tiers,id',
        ], [
            'newCustomerName.required' => 'Nama pelanggan wajib diisi.',
            'newCustomerEmail.email' => 'Format email tidak valid.',
        ]);

        $customer = $createCustomerAction->execute([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'address' => $this->newCustomerAddress,
            'pricing_tier_id' => $this->newCustomerPricingTierId,
            'is_loyalty_member' => true,
            'loyalty_points' => 0,
        ]);

        Notification::make()
            ->title('Pelanggan Berhasil Didaftarkan')
            ->body("Pelanggan {$customer->name} telah berhasil disimpan dan terpilih.")
            ->success()
            ->send();

        cache()->forget('pos_default_customers_' . $this->customerLimit);
        $this->selectCustomer($customer->id, $customer->name, $customer->is_loyalty_member);
        $this->showCreateCustomerModal = false;
    }
}
