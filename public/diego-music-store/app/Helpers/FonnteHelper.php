<?php

namespace App\Helpers;

use App\Models\Branch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    /**
     * Send WhatsApp text message using Fonnte API synchronously.
     *
     * @param string $target Target phone number (e.g. 081234567890 / 6281234567890)
     * @param string $message Message text content
     * @param string|null $token Custom Fonnte API Token (optional, falls back to branch/config)
     * @param Branch|null $branch Optional branch instance to retrieve branch token
     * @return array Response payload status summary
     */
    public static function sendTextMessage(
        string $target,
        string $message,
        ?string $token = null,
        ?Branch $branch = null
    ): array {
        $resolvedToken = static::resolveToken($token, $branch);

        if (empty($resolvedToken)) {
            return [
                'status' => false,
                'message' => 'Token API Fonnte belum dikonfigurasi untuk cabang ini.',
                'detail' => null,
            ];
        }

        $formattedTarget = static::formatPhoneNumber($target);

        if (empty($formattedTarget)) {
            return [
                'status' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
                'detail' => null,
            ];
        }

        $apiUrl = config('services.fonnte.url', 'https://api.fonnte.com/send');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => $resolvedToken,
                ])
                ->post($apiUrl, [
                    'target' => $formattedTarget,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            $json = $response->json();

            $isSuccess = $response->successful() && isset($json['status']) && $json['status'] === true;

            if ($isSuccess) {
                return [
                    'status' => true,
                    'message' => 'Pesan WhatsApp berhasil dikirim ke ' . $formattedTarget,
                    'detail' => $json,
                ];
            }

            $errorMessage = $json['reason'] ?? ($json['detail'] ?? ($json['message'] ?? 'Gagal mengirim pesan WhatsApp via Fonnte API.'));

            return [
                'status' => false,
                'message' => $errorMessage,
                'detail' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Fonnte WhatsApp API Error: ' . $e->getMessage(), [
                'target' => $formattedTarget,
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'message' => 'Terjadi kesalahan koneksi ke server Fonnte: ' . $e->getMessage(),
                'detail' => null,
            ];
        }
    }

    /**
     * Send WhatsApp file / document (e.g. PDF receipt) using Fonnte API synchronously.
     *
     * @param string $target Target phone number (e.g. 081234567890)
     * @param string $fileContent Binary content of the file
     * @param string $filename Name of the file (e.g. Struk-INV-0001.pdf)
     * @param string $caption Optional caption/message accompanying the file
     * @param string|Branch|null $token Custom Fonnte API Token or Branch instance
     * @param Branch|null $branch Optional branch instance to retrieve branch token
     * @return array Response payload status summary
     */
    public static function sendFileMessage(
        string $target,
        string $fileContent,
        string $filename,
        string $caption = '',
        string|Branch|null $token = null,
        ?Branch $branch = null
    ): array {
        $resolvedToken = static::resolveToken($token, $branch);

        if (empty($resolvedToken)) {
            return [
                'status' => false,
                'message' => 'Token API Fonnte belum dikonfigurasi untuk cabang ini.',
                'detail' => null,
            ];
        }

        $formattedTarget = static::formatPhoneNumber($target);

        if (empty($formattedTarget)) {
            return [
                'status' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
                'detail' => null,
            ];
        }

        $apiUrl = config('services.fonnte.url', 'https://api.fonnte.com/send');

        try {
            $response = Http::timeout(25)
                ->withHeaders([
                    'Authorization' => $resolvedToken,
                ])
                ->attach('file', $fileContent, $filename)
                ->post($apiUrl, [
                    'target' => $formattedTarget,
                    'message' => $caption,
                    'filename' => $filename,
                    'countryCode' => '62',
                ]);

            $json = $response->json();
            $isSuccess = $response->successful() && isset($json['status']) && $json['status'] === true;

            if ($isSuccess) {
                return [
                    'status' => true,
                    'message' => 'File PDF struk berhasil dikirim ke ' . $formattedTarget,
                    'detail' => $json,
                ];
            }

            $errorMessage = $json['reason'] ?? ($json['detail'] ?? ($json['message'] ?? 'Gagal mengirim file struk via Fonnte API.'));

            return [
                'status' => false,
                'message' => $errorMessage,
                'detail' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Fonnte WhatsApp File Send Error: ' . $e->getMessage(), [
                'target' => $formattedTarget,
                'filename' => $filename,
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'message' => 'Terjadi kesalahan koneksi saat mengirim file ke Fonnte: ' . $e->getMessage(),
                'detail' => null,
            ];
        }
    }

    /**
     * Check Fonnte device status for a given token.
     *
     * @param string|null $token
     * @param Branch|null $branch
     * @return array
     */
    public static function checkDeviceStatus(?string $token = null, ?Branch $branch = null): array
    {
        $resolvedToken = static::resolveToken($token, $branch);

        if (empty($resolvedToken)) {
            return [
                'status' => false,
                'message' => 'Token API Fonnte tidak ditemukan.',
                'detail' => null,
            ];
        }

        $apiUrl = config('services.fonnte.device_url', 'https://api.fonnte.com/device');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $resolvedToken,
                ])
                ->post($apiUrl);

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && $json['status'] === true) {
                $rawStatus = strtolower($json['device_status'] ?? '');
                $isConnected = ($rawStatus === 'connect');

                if ($isConnected) {
                    return [
                        'status' => true,
                        'message' => 'Perangkat WA Terhubung (Connected)',
                        'device_name' => $json['device'] ?? 'Fonnte Device',
                        'device_status' => 'connect',
                        'quota' => $json['quota'] ?? 0,
                        'detail' => $json,
                    ];
                }

                return [
                    'status' => false,
                    'message' => 'Perangkat WA Terputus (Status: ' . ($json['device_status'] ?? 'disconnect') . '). Silakan scan QR code ulang di dashboard Fonnte.',
                    'device_name' => $json['device'] ?? 'Fonnte Device',
                    'device_status' => $json['device_status'] ?? 'disconnect',
                    'quota' => $json['quota'] ?? 0,
                    'detail' => $json,
                ];
            }

            return [
                'status' => false,
                'message' => $json['reason'] ?? ($json['detail'] ?? 'Perangkat WA Terputus (Disconnected)'),
                'detail' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'Gagal memeriksa status device: ' . $e->getMessage(),
                'detail' => null,
            ];
        }
    }

    /**
     * Resolve Fonnte API token from explicit token, branch, or global config.
     */
    public static function resolveToken(string|Branch|null $tokenOrBranch = null, ?Branch $branch = null): ?string
    {
        if ($tokenOrBranch instanceof Branch) {
            $branch = $tokenOrBranch;
            $tokenOrBranch = null;
        }

        if (!empty($tokenOrBranch) && is_string($tokenOrBranch)) {
            return trim($tokenOrBranch);
        }

        if ($branch && !empty($branch->fonnte_token)) {
            return trim($branch->fonnte_token);
        }

        $activeBranchId = BranchHelper::getActiveBranchId();
        if ($activeBranchId) {
            $activeBranch = Branch::find($activeBranchId);
            if ($activeBranch && !empty($activeBranch->fonnte_token)) {
                return trim($activeBranch->fonnte_token);
            }
        }

        return config('services.fonnte.token');
    }

    /**
     * Format phone number to clean Indonesian standard (08... or 628...).
     */
    public static function formatPhoneNumber(string $number): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        if (empty($cleaned)) {
            return '';
        }

        if (str_starts_with($cleaned, '0')) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '62')) {
            return '0' . substr($cleaned, 2);
        }

        return $cleaned;
    }
}
