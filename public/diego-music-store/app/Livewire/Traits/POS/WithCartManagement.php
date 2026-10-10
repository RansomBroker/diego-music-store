<?php

namespace App\Livewire\Traits\POS;

use App\Models\ProductVariant;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait WithCartManagement
{
    public function setCategory($category)
    {
        $this->activeCategory = $category;
        $this->productPage = 1;
    }

    public function addToCart($variantId)
    {
        $variant = ProductVariant::with([
            'product:id,name,type,category',
            'branchStocks:id,product_variant_id,branch_id,stock',
            'tierPrices:id,product_variant_id,pricing_tier_id,price',
        ])->findOrFail($variantId);
        
        // Check stock for physical products and bundles
        if ($variant->product->isPhysical() || $variant->product->isBundle()) {
            $stock = $variant->stockForBranch($this->selectedBranchId);
            $currentInCart = $this->cart[$variantId]['qty'] ?? 0;
            if ($stock <= $currentInCart) {
                Notification::make()
                    ->title('Stok Tidak Cukup')
                    ->body("Stok untuk {$variant->product->name} ({$variant->name}) tersisa {$stock} pcs.")
                    ->warning()
                    ->send();
                return;
            }
        }

        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['qty']++;
            $this->recalculateItemDiscountAmount($variantId);
            $this->recalculateItemTaxAmount($variantId);
        } else {
            $name = $variant->product->name;
            if ($variant->name) {
                $name .= ' (' . $variant->name . ')';
            }
            $tierId = $this->selectedPricingTierId;
            if ($tierId === 'custom') {
                $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')->first() ?? \App\Models\PricingTier::first();
                $tierId = $defaultTier ? $defaultTier->id : null;
            }

            $this->cart[$variantId] = [
                'variant_id' => $variant->id,
                'name' => $name,
                'price' => $variant->priceForTier($tierId),
                'qty' => 1,
                'type' => $variant->product->type,
                'category' => $variant->product->category ?? null,
                'emoji' => $variant->product->isService() ? '🛠️' : ($variant->product->isBundle() ? '📦' : '🎸'),
                'notes' => '',
                'discount_value' => $variant->discount_value ?? 0,
                'discount_type' => (!empty($variant->discount_value) && $variant->discount_value > 0) ? ($variant->discount_type ?? 'percent') : 'percent',
                'discount_amount' => 0,
                'tax_value' => $variant->tax_value ?? 0,
                'tax_type' => $variant->tax_type ?? 'percent',
                'tax_amount' => 0,
                'pricing_tier_id' => $tierId,
                'sales_rep_id' => null, // Optional item-level sales rep
            ];
            $this->recalculateItemDiscountAmount($variantId);
            $this->recalculateItemTaxAmount($variantId);
        }

        $this->autoSaveDraft();
    }

    public function updateQty($variantId, $change)
    {
        if (!isset($this->cart[$variantId])) {
            return;
        }

        $newQty = $this->cart[$variantId]['qty'] + $change;

        if ($newQty <= 0) {
            unset($this->cart[$variantId]);
            return;
        }

        // Check stock only when increasing quantity (skip DB query entirely when decreasing)
        if ($change > 0) {
            $variant = ProductVariant::with([
                'product:id,name,type',
                'branchStocks:id,product_variant_id,branch_id,stock',
                'bundleItems.childVariant.branchStocks' => fn($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock'),
            ])->findOrFail($variantId);

            if (($variant->product->isPhysical() || $variant->product->isBundle())) {
                $stock = $variant->stockForBranch($this->selectedBranchId);
                if ($stock < $newQty) {
                    Notification::make()
                        ->title('Stok Tidak Cukup')
                        ->body("Stok untuk {$variant->product->name} tersisa {$stock} pcs.")
                        ->warning()
                        ->send();
                    return;
                }
            }
        }

        $this->cart[$variantId]['qty'] = $newQty;
        $this->recalculateItemDiscountAmount($variantId);
        $this->recalculateItemTaxAmount($variantId);

        $this->autoSaveDraft();
    }

    public function updateItemNote($variantId, $note)
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['notes'] = $note;
        }
    }

    public function updateItemPricingTier($variantId, $tierId)
    {
        if (isset($this->cart[$variantId])) {
            $variant = ProductVariant::with('tierPrices')->find($variantId);
            if ($variant) {
                $this->cart[$variantId]['pricing_tier_id'] = $tierId;
                $this->cart[$variantId]['price'] = $variant->priceForTier($tierId);
                $this->recalculateItemDiscountAmount($variantId);
                $this->recalculateItemTaxAmount($variantId);
                $this->selectedPricingTierId = 'custom';
            }
        }
    }

    public function updateItemSalesRep($variantId, $salesRepId)
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['sales_rep_id'] = empty($salesRepId) ? null : $salesRepId;
            $this->autoSaveDraft();
        }
    }

    public function updateItemDiscountValue($variantId, $value)
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['discount_value'] = max(0, intval($value));
            $this->recalculateItemDiscountAmount($variantId);
            $this->recalculateItemTaxAmount($variantId);
        }
    }

    public function toggleItemDiscountType($variantId)
    {
        if (isset($this->cart[$variantId])) {
            $currentType = $this->cart[$variantId]['discount_type'] ?? 'percent';
            $this->cart[$variantId]['discount_type'] = $currentType === 'percent' ? 'fixed' : 'percent';
            $this->recalculateItemDiscountAmount($variantId);
            $this->recalculateItemTaxAmount($variantId);
        }
    }

    public function updateItemTaxValue($variantId, $value)
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['tax_value'] = max(0, floatval($value));
            $this->recalculateItemTaxAmount($variantId);
        }
    }

    public function toggleItemTaxType($variantId)
    {
        if (isset($this->cart[$variantId])) {
            $currentType = $this->cart[$variantId]['tax_type'] ?? 'percent';
            $this->cart[$variantId]['tax_type'] = $currentType === 'fixed' ? 'percent' : 'fixed';
            $this->recalculateItemTaxAmount($variantId);
        }
    }

    protected function recalculateItemDiscountAmount($variantId)
    {
        if (isset($this->cart[$variantId])) {
            $item = &$this->cart[$variantId];
            $value = intval($item['discount_value'] ?? 0);
            $type = $item['discount_type'] ?? 'percent';
            $price = intval($item['price'] ?? 0);
            $qty = intval($item['qty'] ?? 1);

            if ($type === 'percent') {
                $item['discount_amount'] = intval(($price * $qty) * ($value / 100));
            } else {
                $item['discount_amount'] = $value;
            }
        }
    }

    protected function recalculateItemTaxAmount($variantId)
    {
        if (isset($this->cart[$variantId])) {
            $item = &$this->cart[$variantId];
            $value = floatval($item['tax_value'] ?? 0);
            $type = $item['tax_type'] ?? 'percent';
            $price = intval($item['price'] ?? 0);
            $qty = intval($item['qty'] ?? 1);
            $discount = intval($item['discount_amount'] ?? 0);

            if ($type === 'percent') {
                $item['tax_amount'] = intval((($price * $qty) - $discount) * ($value / 100));
            } else {
                $item['tax_amount'] = intval($value * $qty);
            }
        }
    }

    public function getSubtotalProperty()
    {
        $sum = 0;
        foreach ($this->cart as $item) {
            $itemDiscount = intval($item['discount_amount'] ?? 0);
            $sum += ($item['price'] * $item['qty']) - $itemDiscount;
        }
        return $sum;
    }

    public function getDiscountAmountProperty()
    {
        if ($this->discountType === 'percent') {
            return intval($this->subtotal * ($this->discountValue / 100));
        }
        return intval($this->discountValue);
    }

    public function toggleGlobalDiscountType()
    {
        $this->discountType = $this->discountType === 'fixed' ? 'percent' : 'fixed';
    }

    public function getTaxAmountProperty()
    {
        if (!$this->enableTax) {
            return 0;
        }
        $amountAfterDiscount = $this->subtotal - $this->discountAmount;
        return intval($amountAfterDiscount * ($this->taxPercent / 100));
    }

    public function getGrandTotalProperty()
    {
        $tax = $this->taxAmount;
        $total = ($this->subtotal - $this->discountAmount) + $tax;
        return max(0, $total);
    }

    public function getPointDiscountAmountProperty()
    {
        if (!$this->usePoints || !$this->selectedCustomerId || $this->customerPoints <= 0) {
            return 0;
        }

        $tax = $this->taxAmount;
        $maxApplicablePoints = floor((($this->subtotal - $this->discountAmount) + $tax) / self::POINT_VALUATION);
        $pointsToUse = min($this->customerPoints, $maxApplicablePoints);

        return $pointsToUse * self::POINT_VALUATION;
    }
    
    public function clearCart()
    {
        $this->cart = [];
        $this->clearCustomer();
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->usePoints = false;
        $this->selectedSalesRepId = Auth::id();
        $this->selectedSalesRepName = Auth::user() ? Auth::user()->name : '';
        $this->salesSearch = '';
        $defaultCategory = \App\Models\SaleCategory::first();
        $this->saleCategory = $defaultCategory ? $defaultCategory->name : 'Store';

        $this->autoSaveDraft();

        Notification::make()
            ->title('Keranjang Direset')
            ->body('Seluruh item belanja dan data pelanggan telah dikosongkan.')
            ->info()
            ->send();
    }
}
