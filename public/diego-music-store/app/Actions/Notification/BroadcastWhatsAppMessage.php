<?php

namespace App\Actions\Notification;

use App\Helpers\BranchHelper;
use App\Helpers\FonnteHelper;
use App\Helpers\FormatHelper;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class BroadcastWhatsAppMessage
{
    /**
     * Send broadcast WhatsApp messages to selected customers synchronously.
     *
     * @param  string  $messageTemplate
     * @param  string  $targetGroup  'all' | 'loyalty_members' | 'label' | 'tier' | 'with_debt'
     * @param  array   $filterParams  ['customer_label_id' => int, 'pricing_tier_id' => int]
     * @param  Branch|null  $branch
     * @return array [
     *    'success' => bool,
     *    'message' => string,
     *    'total_recipients' => int,
     *    'sent_count' => int,
     *    'failed_count' => int,
     *    'failures' => array
     * ]
     */
    public function execute(
        string $messageTemplate,
        string $targetGroup = 'all',
        array $filterParams = [],
        ?Branch $branch = null
    ): array {
        // 1. Resolve branch
        if (!$branch) {
            $activeBranchId = BranchHelper::getActiveBranchId();
            $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();
        }

        if (!$branch) {
            return [
                'success' => false,
                'message' => 'Cabang toko tidak ditemukan.',
                'total_recipients' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'failures' => [],
            ];
        }

        // 2. Check branch WhatsApp status
        if (!$branch->is_whatsapp_enabled) {
            return [
                'success' => false,
                'message' => "Layanan WhatsApp dinonaktifkan untuk cabang {$branch->name}.",
                'total_recipients' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'failures' => [],
            ];
        }

        $token = FonnteHelper::resolveToken($branch);
        if (empty($token)) {
            return [
                'success' => false,
                'message' => "Token API Fonnte belum dikonfigurasi untuk cabang {$branch->name}.",
                'total_recipients' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'failures' => [],
            ];
        }

        // 3. Query customers with a non-empty phone
        $query = Customer::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        switch ($targetGroup) {
            case 'loyalty_members':
                $query->where('is_loyalty_member', true);
                break;

            case 'label':
                if (!empty($filterParams['customer_label_id'])) {
                    $query->where('customer_label_id', $filterParams['customer_label_id']);
                }
                break;

            case 'tier':
                if (!empty($filterParams['pricing_tier_id'])) {
                    $query->where('pricing_tier_id', $filterParams['pricing_tier_id']);
                }
                break;

            case 'with_debt':
                // Will filter in PHP using total_piutang
                break;

            case 'all':
            default:
                // No extra filter
                break;
        }

        $customers = $query->get();

        if ($targetGroup === 'with_debt') {
            $customers = $customers->filter(function ($customer) {
                return $customer->total_piutang > 0;
            });
        }

        $totalRecipients = $customers->count();
        if ($totalRecipients === 0) {
            return [
                'success' => false,
                'message' => 'Tidak ada pelanggan yang memenuhi kriteria penerima dengan nomor telepon valid.',
                'total_recipients' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'failures' => [],
            ];
        }

        $sentCount = 0;
        $failedCount = 0;
        $failures = [];

        // 4. Send synchronously to each recipient
        foreach ($customers as $index => $customer) {
            // Optional micro-pause between dispatches to respect rate limits
            if ($index > 0) {
                usleep(100000); // 100ms
            }

            $personalizedMessage = $this->renderMessage($messageTemplate, $customer, $branch);

            $result = FonnteHelper::sendTextMessage($customer->phone, $personalizedMessage, $token, $branch);

            if (!empty($result['status'])) {
                $sentCount++;
            } else {
                $failedCount++;
                $failures[] = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'phone' => $customer->phone,
                    'reason' => $result['message'] ?? 'Gagal mengirim pesan',
                ];
                Log::warning("WhatsApp broadcast failed for customer {$customer->name} ({$customer->phone}): " . ($result['message'] ?? 'Unknown error'));
            }
        }

        $isOverallSuccess = $sentCount > 0;
        $message = "Broadcast selesai: {$sentCount} berhasil terkirim" . ($failedCount > 0 ? ", {$failedCount} gagal." : ".");

        return [
            'success' => $isOverallSuccess,
            'message' => $message,
            'total_recipients' => $totalRecipients,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'failures' => $failures,
        ];
    }

    /**
     * Replace dynamic tags in template with customer and branch data.
     */
    public function renderMessage(string $template, Customer $customer, Branch $branch): string
    {
        $piutangStr = 'Rp ' . number_format($customer->total_piutang, 0, ',', '.');
        $pointsStr = number_format($customer->loyalty_points ?? 0, 0, ',', '.');

        $replacements = [
            '{nama}' => $customer->name ?? 'Pelanggan',
            '{toko}' => $branch->name ?? 'Diego Music Store',
            '{telepon}' => $customer->phone ?? '',
            '{poin}' => $pointsStr,
            '{piutang}' => $piutangStr,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
