<?php

namespace App\Actions\Settings;

use Exception;
use Illuminate\Support\Facades\Storage;

class DeleteDatabaseBackup
{
    /**
     * Delete a database backup file from storage disk.
     *
     * @param  string  $filePath
     * @return bool
     * @throws Exception
     */
    public function execute(string $filePath): bool
    {
        $diskName = config('backup.backup.destination.disks.0', 'local');
        $disk = Storage::disk($diskName);

        if (! $disk->exists($filePath)) {
            throw new Exception("File backup {$filePath} tidak ditemukan.");
        }

        return $disk->delete($filePath);
    }
}
