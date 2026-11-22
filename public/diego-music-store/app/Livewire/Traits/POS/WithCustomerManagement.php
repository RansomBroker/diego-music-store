<?php

namespace App\Livewire\Traits\POS;

use App\Helpers\PermissionHelper;
use App\Models\Customer;
use App\Actions\Customer\CreateCustomer;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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

        // Automatically set pricing tier and sale category if registered for this customer
        $customer = Customer::with('saleCategory')->find($id);
        if ($customer) {
            $this->customerPoints = $customer->loyalty_points;
            $this->customerPhone = $customer->phone ?? '';
            if ($customer->pricing_tier_id) {
                $this->selectedPricingTierId = $customer->pricing_tier_id;
            } else {
                $this->selectedPricingTierId = $this->getDefaultRetailPricingTierId();
            }
            if ($customer->saleCategory) {
                $this->saleCategory = $customer->saleCategory->name;
                $this->updatedSaleCategory();
            }
        } else {
            $this->customerPoints = 0;
            $this->selectedPricingTierId = $this->getDefaultRetailPricingTierId();
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

        $this->selectedPricingTierId = $this->getDefaultRetailPricingTierId();
        $this->updatedSelectedPricingTierId($this->selectedPricingTierId);

        $this->saleCategory = $this->getDefaultSaleCategoryName();
        $this->updatedSaleCategory();
    }

    public function openCreateCustomerModal()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.customers.create'), 403);
        $this->newCustomerName = $this->customerSearch;
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerAddress = '';
        
        $this->newCustomerPricingTierId = $this->getDefaultRetailPricingTierId();
        $this->newCustomerSaleCategoryId = $this->saleCategories->firstWhere('name', $this->saleCategory)?->id;
        $this->newCustomerIsLoyaltyMember = true;
        
        $this->showCreateCustomerModal = true;
    }

    public function createCustomer(CreateCustomer $createCustomerAction)
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.customers.create'), 403);
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'nullable|string|max:255',
            'newCustomerEmail' => 'nullable|email|max:255',
            'newCustomerAddress' => 'nullable|string|max:1000',
            'newCustomerPricingTierId' => 'nullable|exists:pricing_tiers,id',
            'newCustomerSaleCategoryId' => 'nullable|exists:sale_categories,id',
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
            'sale_category_id' => $this->newCustomerSaleCategoryId ?: null,
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
