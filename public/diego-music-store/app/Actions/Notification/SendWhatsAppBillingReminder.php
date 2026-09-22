<?php

namespace App\Actions\Notification;

use App\Helpers\BranchHelper;
use App\Helpers\FonnteHelper;
use App\Helpers\FormatHelper;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBillingReminder
{
    /**
     * Send billing/piutang reminder to a specific customer via WhatsApp synchronously.
     *
     * @param  Customer  $customer
     * @param  Branch|null  $branch
     * @return array [ 'success' => bool, 'message' => string, 'detail' => mixed ]
     */
    public function execute(Customer $customer, ?Branch $branch = null): array
    {
        // 1. Validate customer phone
        $phone = $customer->phone;
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => "Pelanggan {$customer->name} tidak memiliki nomor telepon/WhatsApp.",
            ];
        }

        // 2. Resolve branch
        if (!$branch) {
            $activeBranchId = BranchHelper::getActiveBranchId();
            $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();
        }

        if (!$branch) {
            return [
                'success' => false,
                'message' => 'Cabang toko tidak ditemukan.',
            ];
        }

        // 3. Check WhatsApp enabled
        if (!$branch->is_whatsapp_enabled) {
            return [
                'success' => false,
                'message' => "Layanan WhatsApp dinonaktifkan untuk cabang {$branch->name}.",
            ];
        }

        // 4. Resolve token
        $token = FonnteHelper::resolveToken($branch);
        if (empty($token)) {
            return [
                'success' => false,
                'message' => "Token API Fonnte belum dikonfigurasi untuk cabang {$branch->name}.",
            ];
        }

        // 5. Calculate customer outstanding debt
        $totalPiutang = $customer->total_piutang;
        if ($totalPiutang <= 0) {
            return [
                'success' => false,
                'message' => "Pelanggan {$customer->name} tidak memiliki saldo piutang tertunggak (lunas).",
            ];
        }

        // 6. Compose formatted billing message
        $message = $this->buildMessage($customer, $branch);

        // 7. Send synchronously via FonnteHelper
        $result = FonnteHelper::sendTextMessage($phone, $message, $token, $branch);
        $isSuccess = (bool) ($result['status'] ?? false);

        if (!$isSuccess) {
            Log::warning("Gagal mengirim pengingat tagihan WA ke {$customer->name} ({$phone}): " . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => $isSuccess,
            'status'  => $isSuccess,
            'message' => $result['message'] ?? ($isSuccess ? 'Pengingat tagihan berhasil dikirim ke WhatsApp pelanggan.' : 'Gagal mengirim pengingat tagihan via WhatsApp.'),
            'detail'  => $result['detail'] ?? null,
        ];
    }

    /**
     * Build formatted billing reminder message text.
     */
    public function buildMessage(Customer $customer, Branch $branch): string
    {
        $totalPiutang = $customer->total_piutang;
        $unpaidSales = Sale::where('customer_id', $customer->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get();

        $storeName = $branch->store_name ?: ($branch->name ?: 'Diego Music Store');
        $storePhone = $branch->phone ?: ($branch->fonnte_whatsapp_number ?? '');

        $lines = [];
        $lines[] = "📢 *PEMBERITAHUAN TAGIHAN*";
        $lines[] = "*{$storeName}*";
        if (!empty($storePhone)) {
            $lines[] = "Kontak: {$storePhone}";
        }
        $lines[] = str_repeat('-', 28);
        $lines[] = "Kepada Yth: *{$customer->name}*";
        $lines[] = "Tanggal   : " . now()->format('d/m/Y');
        $lines[] = str_repeat('-', 28);
        $lines[] = "Kami menginformasikan bahwa saat ini terdapat kewajiban tagihan tertunggak sebesar:";
        $lines[] = "💰 *TOTAL TAGIHAN: " . FormatHelper::rupiah($totalPiutang) . "*";
        $lines[] = str_repeat('-', 28);

        if ($unpaidSales->isNotEmpty()) {
            $lines[] = "*Riwayat Faktur Terakhir:*";
            foreach ($unpaidSales as $sale) {
                $invDate = $sale->invoice_date ? $sale->invoice_date->format('d/m/Y') : $sale->created_at->format('d/m/Y');
                $lines[] = "• #{$sale->invoice_number} ({$invDate}): " . FormatHelper::rupiah($sale->grand_total);
            }
            $lines[] = str_repeat('-', 28);
        }

        $lines[] = "Pembayaran dapat diselesaikan langsung di kasir toko atau transfer bank cabang kami.";
        $lines[] = "Jika telah melakukan pembayaran, silakan abaikan pesan ini atau kirimkan bukti transfer ke nomor ini.";
        $lines[] = "";
        $lines[] = "🙏 _Terima kasih atas perhatian dan kerja sama Anda._";

        return implode("\n", $lines);
    }
}
