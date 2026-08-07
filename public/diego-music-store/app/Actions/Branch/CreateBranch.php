<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use App\Models\ReceiptSetting;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateBranch
{
    /**
     * Execute new Branch creation with automatic initial configuration:
     * - Creates Branch model
     * - Attaches Owner / Admin users to pivot branch_user
     * - Creates default ReceiptSetting for the new branch
     * - Initializes ProductBranchStock (stock = 0) for all active product variants
     *
     * @param array $data
     * @return Branch
     */
    public static function execute(array $data): Branch
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Branch record
            $branch = Branch::create([
                'name'           => $data['name'],
                'store_name'     => $data['store_name'] ?? 'Diego Music Store',
                'logo_path'      => $data['logo_path'] ?? null,
                'address'        => $data['address'] ?? null,
                'phone'          => $data['phone'] ?? null,
                'email'          => $data['email'] ?? null,
                'city'           => $data['city'] ?? null,
                'province'       => $data['province'] ?? null,
                'postal_code'    => $data['postal_code'] ?? null,
                'npwp'           => $data['npwp'] ?? null,
                'bank_info'      => $data['bank_info'] ?? null,
                'receipt_header' => $data['receipt_header'] ?? null,
                'receipt_footer' => $data['receipt_footer'] ?? null,
                'manager_id'     => $data['manager_id'] ?? null,
                'is_active'      => $data['is_active'] ?? true,
            ]);

            // 2. Attach Owner / Admin users to branch_user pivot automatically
            $adminUserIds = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);
            })->pluck('id')->toArray();

            $staffIds = [];
            if (!empty($data['users']) && is_array($data['users'])) {
                $staffIds = $data['users'];
            } elseif (!empty($data['user_ids']) && is_array($data['user_ids'])) {
                $staffIds = $data['user_ids'];
            }

            $assignUserIds = array_unique(array_merge($adminUserIds, $staffIds));

            if (!empty($assignUserIds)) {
                $branch->users()->syncWithoutDetaching($assignUserIds);
            }

            // 3. Create default ReceiptSetting for the new branch
            ReceiptSetting::firstOrCreate(
                ['branch_id' => $branch->id],
                [
                    'store_display_name' => $branch->store_name ?: 'Diego Music Store',
                    'paper_width'        => '80mm',
                    'show_logo'          => true,
                    'show_cashier'       => true,
                    'show_customer'      => true,
                    'show_tax_details'   => true,
                    'header_text'        => 'Terima Kasih Atas Kunjungan Anda',
                    'footer_text'        => "Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.\nSimpan struk ini sebagai bukti pembayaran resmi.",
                ]
            );

            // 4. Initialize zero stock records (ProductBranchStock) for all active product variants
            $activeVariants = ProductVariant::whereHas('product', function ($pq) {
                $pq->where('is_active', true);
            })->get();

            foreach ($activeVariants as $variant) {
                ProductBranchStock::firstOrCreate(
                    [
                        'product_variant_id' => $variant->id,
                        'branch_id'          => $branch->id,
                    ],
                    [
                        'stock' => 0,
                        'hpp'   => $variant->hpp ?: $variant->cost_price ?: 0,
                    ]
                );
            }

            return $branch;
        });
    }
}
