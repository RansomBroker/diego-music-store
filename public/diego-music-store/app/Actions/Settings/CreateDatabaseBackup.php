<?php

namespace App\Actions\Settings;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CreateDatabaseBackup
{
    /**
     * Execute Database Backup Action via Spatie Laravel Backup or PHP PDO Fallback.
     *
     * @param  bool  $onlyDb
     * @return array{success: bool, message: string, output: string}
     */
    public function execute(bool $onlyDb = true): array
    {
        try {
            $options = [];
            if ($onlyDb) {
                $options['--only-db'] = true;
            }

            $exitCode = Artisan::call('backup:run', $options);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return [
                    'success' => true,
                    'message' => 'Proses backup database berhasil dibuat.',
                    'output'  => $output,
                ];
            }

            // If spatie backup failed due to missing CLI binary (mysqldump / sqlite3), execute PHP PDO Fallback
            if (str_contains($output, 'not found') || str_contains($output, '127') || $exitCode !== 0) {
                Log::info('Spatie backup binary not found, running PHP PDO Database Exporter fallback...');
                $fallbackSuccess = $this->createPhpPdoBackup();

                if ($fallbackSuccess) {
                    return [
                        'success' => true,
                        'message' => 'Proses backup database berhasil dibuat (PHP Exporter).',
                        'output'  => 'Backup created via PHP PDO Exporter.',
                    ];
                }
            }

            Log::error('Database Backup failed', ['exitCode' => $exitCode, 'output' => $output]);

            return [
                'success' => false,
                'message' => 'Gagal membuat backup database. Periksa log aplikasi.',
                'output'  => $output,
            ];
        } catch (Exception $e) {
            Log::info('Exception during Spatie backup, running PHP PDO Database Exporter fallback...', ['error' => $e->getMessage()]);
            
            try {
                $fallbackSuccess = $this->createPhpPdoBackup();

                if ($fallbackSuccess) {
                    return [
                        'success' => true,
                        'message' => 'Proses backup database berhasil dibuat (PHP Exporter).',
                        'output'  => 'Backup created via PHP PDO Exporter.',
                    ];
                }
            } catch (Exception $fallbackEx) {
                Log::error('PHP PDO Backup Exception', ['exception' => $fallbackEx->getMessage()]);
            }

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'output'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Fallback database backup generator using PHP PDO & ZipArchive.
     *
     * @return bool
     */
    private function createPhpPdoBackup(): bool
    {
        $diskName = config('backup.backup.destination.disks.0', 'local');
        $appName = config('backup.backup.name', 'laravel-backup');
        $disk = Storage::disk($diskName);

        $filename = now()->format('Y-m-d-H-i-s') . '.zip';
        $relativeZipPath = "{$appName}/{$filename}";
        
        // Ensure destination directory exists
        $disk->makeDirectory($appName);
        $fullZipPath = $disk->path($relativeZipPath);

        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $tables = [];

        if ($driver === 'sqlite') {
            $queryResult = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($queryResult as $row) {
                $tables[] = $row->name;
            }
        } else {
            // MySQL / MariaDB
            $dbName = $connection->getDatabaseName();
            $queryResult = $connection->select('SHOW TABLES');
            foreach ($queryResult as $row) {
                $prop = get_object_vars($row);
                $tables[] = reset($prop);
            }
        }

        $sqlContent = "-- Diego Music Store ERP Database Dump\n";
        $sqlContent .= "-- Generated at: " . now()->toDateTimeString() . "\n";
        $sqlContent .= "-- Driver: {$driver}\n\n";

        if ($driver === 'mysql') {
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        } elseif ($driver === 'sqlite') {
            $sqlContent .= "PRAGMA foreign_keys = OFF;\n\n";
        }

        foreach ($tables as $table) {
            if ($driver === 'sqlite') {
                $row = $connection->selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
                if ($row && isset($row->sql)) {
                    $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $sqlContent .= $row->sql . ";\n\n";
                }
            } else {
                $row = $connection->selectOne("SHOW CREATE TABLE `{$table}`");
                if ($row) {
                    $prop = get_object_vars($row);
                    $createSql = $prop['Create Table'] ?? reset($prop);
                    $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $sqlContent .= $createSql . ";\n\n";
                }
            }

            // Dump table data in chunks
            $rows = $connection->table($table)->get();
            if ($rows->isNotEmpty()) {
                foreach ($rows->chunk(200) as $chunk) {
                    foreach ($chunk as $r) {
                        $values = [];
                        foreach ((array) $r as $val) {
                            if ($val === null) {
                                $values[] = 'NULL';
                            } elseif (is_numeric($val)) {
                                $values[] = $val;
                            } else {
                                $values[] = $connection->getPdo()->quote((string) $val);
                            }
                        }
                        $sqlContent .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
                $sqlContent .= "\n";
            }
        }

        if ($driver === 'mysql') {
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
        } elseif ($driver === 'sqlite') {
            $sqlContent .= "PRAGMA foreign_keys = ON;\n";
        }

        // Write SQL to Zip Archive
        $zip = new ZipArchive();
        if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('db-dump.sql', $sqlContent);
            $zip->close();

            return true;
        }

        return false;
    }
}
