<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Settings\CreateDatabaseBackup;
use App\Actions\Settings\DeleteDatabaseBackup;
use App\Actions\Settings\GetDatabaseBackupList;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DatabaseBackup extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Backup Database';

    protected static ?string $title = 'Manajemen Backup Database';

    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedCircleStack;

    protected string $view = 'filament.pages.settings.database-backup';

    /**
     * @var array<int, array{name: string, path: string, size: string, raw_size: int, date: string, raw_date: int}>
     */
    public array $backups = [];

    public function mount(): void
    {
        $this->loadBackups();
    }

    public function loadBackups(): void
    {
        $action = new GetDatabaseBackupList();
        $this->backups = $action->execute();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Buat Backup Baru')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Buat Backup Database Baru')
                ->modalDescription('Pilih tipe backup yang ingin dijalankan.')
                ->form([
                    Radio::make('backup_type')
                        ->label('Jenis Backup')
                        ->options([
                            'only_db' => 'Database Only (Hanya Skema & Data DB)',
                            'full'    => 'Full Backup (Database & Berkas File/Storage)',
                        ])
                        ->default('only_db')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $onlyDb = ($data['backup_type'] ?? 'only_db') === 'only_db';
                    $action = new CreateDatabaseBackup();
                    $result = $action->execute($onlyDb);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Backup Berhasil')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Backup Gagal')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }

                    $this->loadBackups();
                }),
        ];
    }

    public function downloadBackup(string $filePath)
    {
        $diskName = config('backup.backup.destination.disks.0', 'local');
        $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

        if (! $disk->exists($filePath)) {
            Notification::make()
                ->title('File Tidak Ditemukan')
                ->body('File backup yang diminta tidak ada di storage.')
                ->danger()
                ->send();

            return null;
        }

        return $disk->download($filePath);
    }

    public function deleteBackup(string $filePath): void
    {
        try {
            $action = new DeleteDatabaseBackup();
            $action->execute($filePath);

            Notification::make()
                ->title('Backup Dihapus')
                ->body('File backup berhasil dihapus.')
                ->success()
                ->send();

            $this->loadBackups();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Menghapus')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
