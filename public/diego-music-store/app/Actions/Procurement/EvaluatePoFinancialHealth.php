<?php

namespace App\Actions\Procurement;

use App\Models\Account;
use App\Models\PoSmartAssistSetting;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class EvaluatePoFinancialHealth
{
    /**
     * Execute PO financial health evaluation.
     *
     * @param  float|int  $poGrandTotal
     * @param  int|null  $branchId
     * @param  string|null  $paymentTerm
     * @return array
     */
    public function execute(float|int $poGrandTotal = 0, ?int $branchId = null, ?string $paymentTerm = 'COD'): array
    {
        $settings = PoSmartAssistSetting::getSettings();

        if (! $settings->is_enabled) {
            return [
                'is_enabled' => false,
                'status' => 'DISABLED',
                'risk_level' => 'INFO',
                'badge_label' => 'SMART ASSIST NON-AKTIF',
                'color' => 'gray',
                'total_liquid_cash' => 0,
                'pending_commitments' => 0,
                'net_available_cash' => 0,
                'po_grand_total' => $poGrandTotal,
                'post_po_cash' => 0,
                'buffer_percentage' => 0,
                'sales_30d_inflow' => 0,
                'recommendation' => 'Smart assist sedang dinonaktifkan melalui menu Pengaturan.',
                'tips' => [],
            ];
        }

        // 1. Calculate Liquid Cash & Bank Assets
        $liquidAccountsQuery = Account::query()
            ->where('is_header', false)
            ->where(function ($q) {
                $q->where('classification', 'kas-bank')
                    ->orWhere('code', 'like', '1-11%')
                    ->orWhere('code', 'like', '1-1%');
            });

        $liquidAccounts = $liquidAccountsQuery->get();
        $totalLiquidCash = 0.0;

        foreach ($liquidAccounts as $acc) {
            $balance = (float) $acc->balance;
            if ($balance > 0) {
                $totalLiquidCash += $balance;
            }
        }

        // Ensure total liquid cash is non-negative
        if ($totalLiquidCash < 0) {
            $totalLiquidCash = 0.0;
        }

        // 2. Calculate Pending Obligations (Accounts Payable & Pending POs)
        $pendingCommitments = 0.0;

        // Unpaid Credit Purchase Transactions
        $apQuery = PurchaseTransaction::query()
            ->where('status', 'posted')
            ->where('purchase_type', 'Kredit');

        if ($branchId) {
            $apQuery->where('branch_id', $branchId);
        }

        $creditPurchases = $apQuery->get();
        foreach ($creditPurchases as $pt) {
            $paid = (float) $pt->supplierPaymentItems->sum('amount_paid');
            $unpaid = max(0, (float) $pt->grand_total - $paid);
            $pendingCommitments += $unpaid;
        }

        // Pending Purchase Orders (Draft / Approved)
        if ($settings->include_pending_pos) {
            $poQuery = PurchaseOrder::query()
                ->whereIn('status', ['draft', 'approved']);

            if ($branchId) {
                $poQuery->where('branch_id', $branchId);
            }

            $pendingCommitments += (float) $poQuery->sum('grand_total');
        }

        // 3. Calculate 30-Day Sales Inflow Projection
        $sales30dInflow = 0.0;
        if ($settings->include_sales_projection) {
            $salesQuery = Sale::query()
                ->where('status', 'completed')
                ->whereDate('invoice_date', '>=', Carbon::now()->subDays(30));

            if ($branchId) {
                $salesQuery->where('branch_id', $branchId);
            }

            $sales30dInflow = (float) $salesQuery->sum('grand_total');
        }

        // 4. Working Capital & Post-PO Cash Balance
        $netAvailableCash = max(0, $totalLiquidCash - $pendingCommitments);
        $postPoCash = $netAvailableCash - $poGrandTotal;

        $bufferPercentage = $totalLiquidCash > 0
            ? round(($postPoCash / $totalLiquidCash) * 100, 1)
            : 0;

        // 5. Risk Rules & Status Decision
        $minBufferPct = $settings->min_buffer_percentage ?: 20;
        $minBufferNominal = $settings->min_buffer_nominal ?: 5000000;

        $status = 'AMAN';
        $riskLevel = 'SAFE';
        $badgeLabel = 'AMAN UNTUK DITERBITKAN';
        $color = 'success';
        $recommendation = '';
        $tips = [];

        if ($poGrandTotal <= 0) {
            $status = 'AMAN';
            $riskLevel = 'SAFE';
            $badgeLabel = 'SIAP ANALISIS PO';
            $color = 'info';
            $recommendation = 'Masukkan item barang PO untuk memulai analisis kelayakan keuangan secara otomatis.';
        } elseif ($postPoCash < 0 || $netAvailableCash < $poGrandTotal || $postPoCash < $minBufferNominal) {
            $status = 'TIDAK_AMAN';
            $riskLevel = 'CRITICAL';
            $badgeLabel = 'TIDAK AMAN (DEFISIT KAS)';
            $color = 'danger';

            $deficit = abs($postPoCash);
            $recommendation = 'PERINGATAN KERAS: Pengadaan PO ini berisiko tinggi menyebabkan defisit kas sebesar Rp ' . number_format($deficit, 0, ',', '.') . '. Dana likuid toko tidak mencukupi.';

            $tips[] = 'Ajukan pembayarannya menggunakan Payment Term TOP 30 atau 60 Hari ke supplier.';
            $tips[] = 'Turunkan kuantitas pemesanan produk, prioritaskan produk terlaris (Fast Moving).';
            $tips[] = 'Segera lakukan penagihan piutang usaha berjalan untuk menambah likuiditas kas toko.';
        } elseif ($bufferPercentage < $minBufferPct || ($paymentTerm === 'COD' && $bufferPercentage < 35)) {
            $status = 'WASPADA';
            $riskLevel = 'WARNING';
            $badgeLabel = 'WASPADA (MARGIN KAS TIPIS)';
            $color = 'warning';

            $recommendation = 'PO ini dapat dilakukan, namun sisa kas buffer toko relatif tipis (hanya ' . $bufferPercentage . '% dari total dana likuid).';

            if ($paymentTerm === 'COD') {
                $tips[] = 'Metode pembayaran saat ini COD (Cash on Delivery). Disarankan dinegosiasikan menjadi TOP 14 / 30 Hari.';
            }
            $tips[] = 'Pastikan penjualan 30 hari ke depan tetap stabil untuk menjaga kelancaran operasional.';
            $tips[] = 'Hindari penambahan komitmen beban operasional besar lainnya dalam waktu dekat.';
        } else {
            $status = 'AMAN';
            $riskLevel = 'SAFE';
            $badgeLabel = 'AMAN UNTUK DITERBITKAN';
            $color = 'success';

            $recommendation = 'Kondisi keuangan toko SEHAT. Kas dan likuiditas toko sangat mencukupi untuk membiayai PO ini dengan sisa buffer Rp ' . number_format($postPoCash, 0, ',', '.') . ' (' . $bufferPercentage . '%).';
            $tips[] = 'Disiplin mencatat tanggal jatuh tempo pembayaran agar arus kas toko tetap terjaga seimbang.';
        }

        return [
            'is_enabled' => true,
            'status' => $status,
            'risk_level' => $riskLevel,
            'badge_label' => $badgeLabel,
            'color' => $color,
            'total_liquid_cash' => $totalLiquidCash,
            'pending_commitments' => $pendingCommitments,
            'net_available_cash' => $netAvailableCash,
            'po_grand_total' => $poGrandTotal,
            'post_po_cash' => $postPoCash,
            'buffer_percentage' => $bufferPercentage,
            'sales_30d_inflow' => $sales30dInflow,
            'recommendation' => $recommendation,
            'tips' => $tips,
            'settings' => $settings,
        ];
    }
}
