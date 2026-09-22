<?php

namespace App\Actions\Notification;

use App\Helpers\FonnteHelper;
use App\Helpers\FormatHelper;
use App\Models\Branch;
use App\Models\ReceiptSetting;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReceipt
{
    /**
     * Send receipt message via WhatsApp synchronously using Fonnte.
     *
     * @param  Sale  $sale
     * @param  string|null  $targetPhone
     * @return array [ 'success' => bool, 'message' => string, 'response' => mixed ]
     */
    public function execute(Sale $sale, ?string $targetPhone = null): array
    {
        // 1. Determine target phone number
        $phone = $targetPhone ?: ($sale->customer?->phone ?? null);
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak ditemukan.',
            ];
        }

        // 2. Resolve branch
        $branch = $sale->branch ?: Branch::find($sale->branch_id);
        if (!$branch) {
            return [
                'success' => false,
                'message' => 'Data cabang transaksi tidak ditemukan.',
            ];
        }

        // 3. Check if WhatsApp is enabled for this branch
        if (!$branch->is_whatsapp_enabled) {
            return [
                'success' => false,
                'message' => "Layanan WhatsApp dinonaktifkan untuk cabang {$branch->name}.",
            ];
        }

        // 4. Resolve Fonnte token
        $token = FonnteHelper::resolveToken($branch);
        if (empty($token)) {
            return [
                'success' => false,
                'message' => "Token API Fonnte belum dikonfigurasi untuk cabang {$branch->name}.",
            ];
        }

        // 5. Ensure sale relations are loaded
        $sale->loadMissing(['branch', 'customer', 'salesRep', 'items.variant.product']);

        $setting = ReceiptSetting::where('branch_id', $sale->branch_id)->first();
        $storeName = $setting?->store_display_name ?: ($sale->branch?->store_name ?: ($sale->branch?->name ?: 'Diego Music Store'));
        $customerName = $sale->customer?->name ?? 'Pelanggan';

        // 6. Generate thermal-style PDF receipt
        $pdfHeight = max(480, 260 + (count($sale->items) * 45));
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.receipt-pdf', [
            'sale' => $sale,
            'setting' => $setting,
        ])->setPaper([0, 0, 226.77, $pdfHeight], 'portrait');

        $pdfContent = $pdf->output();
        $filename = "Struk-{$sale->invoice_number}.pdf";

        // 7. Accompanying caption message
        $caption = "🧾 *STRUK PEMBELIAN - {$storeName}*\n"
            . "No. Faktur: *#{$sale->invoice_number}*\n"
            . "Total: *" . FormatHelper::rupiah($sale->grand_total) . "*\n\n"
            . "Halo {$customerName}, terlampir bukti struk digital transaksi Anda dalam format PDF.\n"
            . "Terima kasih telah berbelanja di {$storeName}! 🙏";

        // 8. Send PDF synchronously via FonnteHelper
        $result = FonnteHelper::sendFileMessage($phone, $pdfContent, $filename, $caption, $token, $branch);
        $isSuccess = (bool) ($result['status'] ?? false);

        if (!$isSuccess) {
            Log::warning("Gagal mengirim struk WA PDF untuk faktur {$sale->invoice_number} ke {$phone}: " . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => $isSuccess,
            'status'  => $isSuccess,
            'message' => $result['message'] ?? ($isSuccess ? 'Struk PDF berhasil dikirim ke WhatsApp.' : 'Gagal mengirim file struk via WhatsApp.'),
            'detail'  => $result['detail'] ?? null,
        ];
    }

    /**
     * Build standard formatted receipt text for WhatsApp.
     */
    public function buildReceiptMessage(Sale $sale): string
    {
        $setting = ReceiptSetting::where('branch_id', $sale->branch_id)->first();
        $storeName = $setting?->store_display_name ?: ($sale->branch?->store_name ?: ($sale->branch?->name ?: 'Diego Music Store'));
        $branchName = $sale->branch?->name ?? 'Cabang Pusat';
        $storeAddress = $sale->branch?->address ?? '';
        $storePhone = $sale->branch?->phone ?: ($sale->branch?->fonnte_whatsapp_number ?? '');

        $lines = [];
        $lines[] = "🧾 *STRUK PEMBELIAN*";
        $lines[] = "*{$storeName}*";
        if ($branchName !== $storeName) {
            $lines[] = "{$branchName}";
        }
        if (!empty($storeAddress)) {
            $lines[] = "{$storeAddress}";
        }
        if (!empty($storePhone)) {
            $lines[] = "Telp/WA: {$storePhone}";
        }

        $lines[] = str_repeat('-', 28);
        $lines[] = "No. Faktur : *#{$sale->invoice_number}*";
        $lines[] = "Tanggal    : " . ($sale->created_at ? $sale->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));
        $lines[] = "Kasir      : " . ($sale->salesRep?->name ?? 'Kasir');
        $lines[] = "Pelanggan  : " . ($sale->customer?->name ?? 'Umum / Walk-in');
        $lines[] = str_repeat('-', 28);
        $lines[] = "*RINCIAN ITEM:*";

        foreach ($sale->items as $item) {
            $productName = $item->variant?->product?->name ?? 'Produk';
            $variantName = $item->variant?->name ?? '';
            $fullName = $productName . ($variantName && $variantName !== 'Default' ? " ({$variantName})" : '');
            $qty = $item->quantity;
            $price = FormatHelper::rupiah($item->unit_price);
            $total = FormatHelper::rupiah($item->total_price);

            $lines[] = "• *{$fullName}*";
            $itemDetail = "  {$qty} x {$price} = {$total}";
            if ($item->discount_amount > 0) {
                $itemDetail .= " (Disc: -" . FormatHelper::rupiah($item->discount_amount) . ")";
            }
            $lines[] = $itemDetail;
        }

        $lines[] = str_repeat('-', 28);
        $lines[] = "Subtotal       : " . FormatHelper::rupiah($sale->subtotal);

        if ($sale->discount_amount > 0) {
            $lines[] = "Diskon         : -" . FormatHelper::rupiah($sale->discount_amount);
        }
        if ($sale->tax_amount > 0) {
            $lines[] = "PPN (11%)      : +" . FormatHelper::rupiah($sale->tax_amount);
        }

        $lines[] = "*TOTAL AKHIR   : " . FormatHelper::rupiah($sale->grand_total) . "*";
        $lines[] = "Metode Bayar   : " . strtoupper($sale->payment_method ?: 'TUNAI');

        $lines[] = str_repeat('-', 28);
        if (!empty($setting?->footer_text)) {
            $lines[] = "_{$setting->footer_text}_";
        } else {
            $lines[] = "🙏 _Terima kasih atas kunjungan Anda!_";
            $lines[] = "_Simpan bukti struk ini untuk keperluan garansi & layanan pelanggan._";
        }

        return implode("\n", $lines);
    }
}
