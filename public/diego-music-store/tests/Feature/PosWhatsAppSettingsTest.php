<?php

namespace Tests\Feature;

use App\Actions\Notification\TestFonnteConnection;
use App\Helpers\FonnteHelper;
use App\Livewire\PosWhatsAppSettings;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosWhatsAppSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole('owner');

        $this->branch = Branch::create([
            'name' => 'Cabang Pontianak Test',
            'store_name' => 'Diego Music Pontianak',
            'phone' => '0561-734567',
            'address' => 'Jl. Gajah Mada No. 21',
            'fonnte_token' => 'test-token-12345',
            'fonnte_whatsapp_number' => '081254321098',
            'is_whatsapp_enabled' => true,
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);
    }

    public function test_it_can_render_pos_whatsapp_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('pos.whatsapp-settings'));

        $response->assertStatus(200);
        $response->assertSee('Integrasi WhatsApp API Fonnte');
        $response->assertSee('Cabang Pontianak Test');
    }

    public function test_it_can_update_branch_whatsapp_settings(): void
    {
        Livewire::actingAs($this->user)
            ->test(PosWhatsAppSettings::class)
            ->set('selectedBranchId', $this->branch->id)
            ->set('fonnte_token', 'new-fonnte-token-999')
            ->set('fonnte_whatsapp_number', '08987654321')
            ->set('is_whatsapp_enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->branch->refresh();
        $this->assertEquals('new-fonnte-token-999', $this->branch->fonnte_token);
        $this->assertEquals('08987654321', $this->branch->fonnte_whatsapp_number);
        $this->assertFalse($this->branch->is_whatsapp_enabled);
    }

    public function test_it_can_send_test_whatsapp_message_synchronously(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'detail' => 'Pesan terkirim',
            ], 200),
        ]);

        $action = new TestFonnteConnection();
        $result = $action->execute(
            targetNumber: '081254321098',
            testMessage: 'Pesan Tes WhatsApp',
            branch: $this->branch,
            token: 'test-token-12345'
        );

        $this->assertTrue($result['status']);
        $this->assertStringContainsString('081254321098', $result['message']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send' &&
                $request->hasHeader('Authorization', 'test-token-12345') &&
                $request['target'] === '081254321098' &&
                $request['message'] === 'Pesan Tes WhatsApp';
        });
    }

    public function test_fonnte_helper_formats_phone_numbers_correctly(): void
    {
        $this->assertEquals('081254321098', FonnteHelper::formatPhoneNumber('081254321098'));
        $this->assertEquals('081254321098', FonnteHelper::formatPhoneNumber('6281254321098'));
        $this->assertEquals('081254321098', FonnteHelper::formatPhoneNumber('+62 812-5432-1098'));
    }
}
