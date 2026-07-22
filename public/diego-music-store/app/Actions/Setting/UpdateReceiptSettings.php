<?php

namespace App\Actions\Setting;

use App\Models\ReceiptSetting;
use Illuminate\Support\Facades\DB;

class UpdateReceiptSettings
{
    /**
     * Execute updating or creating receipt & invoice settings for a branch.
     *
     * @param int|null $branchId
     * @param array $data
     * @return ReceiptSetting
     */
    public function execute(?int $branchId, array $data): ReceiptSetting
    {
        return DB::transaction(function () use ($branchId, $data) {
            $setting = ReceiptSetting::firstOrNew(['branch_id' => $branchId]);

            $setting->fill([
                'store_display_name'   => $data['store_display_name'] ?? null,
                'header_text'          => $data['header_text'] ?? null,
                'footer_text'          => $data['footer_text'] ?? null,
                'paper_width'          => $data['paper_width'] ?? '80mm',
                'show_logo'            => (bool) ($data['show_logo'] ?? true),
                'show_customer'        => (bool) ($data['show_customer'] ?? true),
                'show_cashier'         => (bool) ($data['show_cashier'] ?? true),
                'show_tax_details'     => (bool) ($data['show_tax_details'] ?? true),
                'invoice_footer_notes' => $data['invoice_footer_notes'] ?? null,
            ]);

            $setting->save();

            return $setting;
        });
    }
}
