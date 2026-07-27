<?php

namespace App\Actions\Settings;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class GetDatabaseBackupList
{
    /**
     * Get list of database backup files from storage disk.
     *
     * @return array<int, array{name: string, path: string, size: string, raw_size: int, date: string, raw_date: int}>
     */
    public function execute(): array
    {
        $diskName = config('backup.backup.destination.disks.0', 'local');
        $disk = Storage::disk($diskName);
        $appName = config('backup.backup.name', 'laravel-backup');

        $allFiles = $disk->allFiles($appName);
        $backups = [];

        foreach ($allFiles as $filePath) {
            if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'zip') {
                continue;
            }

            $size = $disk->size($filePath);
            $lastModified = $disk->lastModified($filePath);

            $backups[] = [
                'name'     => basename($filePath),
                'path'     => $filePath,
                'size'     => $this->formatBytes($size),
                'raw_size' => $size,
                'date'     => Carbon::createFromTimestamp($lastModified)->format('d M Y H:i:s'),
                'raw_date' => $lastModified,
            ];
        }

        // Sort descending by modified timestamp
        usort($backups, fn ($a, $b) => $b['raw_date'] <=> $a['raw_date']);

        return $backups;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
