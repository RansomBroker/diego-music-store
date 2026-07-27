<?php

namespace Tests\Feature\Filament;

use App\Actions\Settings\CreateDatabaseBackup;
use App\Actions\Settings\DeleteDatabaseBackup;
use App\Actions\Settings\GetDatabaseBackupList;
use App\Filament\Pages\Settings\DatabaseBackup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DatabaseBackupPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_render_database_backup_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(DatabaseBackup::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Backup Database')
            ->assertSee('Riwayat File Backup');
    }

    /** @test */
    public function it_can_list_and_delete_backup_files()
    {
        $diskName = config('backup.backup.destination.disks.0', 'local');
        $appName = config('backup.backup.name', 'laravel-backup');
        $dummyPath = "{$appName}/2026-07-28-00-00-00-test.zip";

        Storage::disk($diskName)->put($dummyPath, 'dummy backup zip content');

        // Test GetDatabaseBackupList Action
        $listAction = new GetDatabaseBackupList();
        $backups = $listAction->execute();

        $this->assertNotEmpty($backups);
        $this->assertEquals('2026-07-28-00-00-00-test.zip', $backups[0]['name']);

        // Test Livewire page displays the file
        Livewire::actingAs($this->user)
            ->test(DatabaseBackup::class)
            ->assertSee('2026-07-28-00-00-00-test.zip');

        // Test DeleteDatabaseBackup Action
        $deleteAction = new DeleteDatabaseBackup();
        $deleted = $deleteAction->execute($dummyPath);

        $this->assertTrue($deleted);
        $this->assertFalse(Storage::disk($diskName)->exists($dummyPath));
    }

    /** @test */
    public function it_can_trigger_create_backup_action()
    {
        Livewire::actingAs($this->user)
            ->test(DatabaseBackup::class)
            ->callAction('createBackup', data: [
                'backup_type' => 'only_db',
            ])
            ->assertHasNoActionErrors();
    }
}
