<?php

namespace Tests\Feature;

use App\Helpers\ProductHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSBarcodePrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_barcode_print_sheet_via_token(): void
    {
        $user = User::factory()->create();

        $payload = [
            'layout' => '3col',
            'label_width' => 33,
            'label_height' => 18,
            'columns' => 3,
            'gap_x' => 3,
            'gap_y' => 3,
            'font_size' => 10,
            'barcode_height' => 35,
            'show_store' => true,
            'show_name' => true,
            'show_price' => true,
            'show_code' => true,
            'queue' => [
                [
                    'variant_id' => 1,
                    'name' => 'Gitar Akustik Yamaha F310',
                    'sku' => 'GTR-YMH-01',
                    'barcode' => '8991234567890',
                    'price' => 1500000,
                    'qty' => 3,
                ],
                [
                    'variant_id' => 2,
                    'name' => 'Senar Gitar D\'Addario EJ15',
                    'sku' => 'ACC-DAD-01',
                    'barcode' => '8990987654321',
                    'price' => 95000,
                    'qty' => 5,
                ],
            ],
        ];

        $token = ProductHelper::storeBarcodePrintPayload($payload);
        $this->assertNotEmpty($token);
        $this->assertStringStartsWith('bc_', $token);

        $response = $this->actingAs($user)->get(route('pos.barcode-print.sheet', ['token' => $token]));

        $response->assertStatus(200);
        $response->assertSee('Gitar Akustik Yamaha F310');
        $response->assertSee('GTR-YMH-01');
        $response->assertSee('Senar Gitar D&#039;Addario EJ15', false);
    }

    public function test_returns_404_when_token_is_missing_or_expired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.barcode-print.sheet', ['token' => 'bc_nonexistent_or_expired']));
        $response->assertStatus(404);
    }

    public function test_backward_compatible_with_data_query_parameter(): void
    {
        $user = User::factory()->create();

        $payload = [
            'layout' => '1col',
            'queue' => [
                [
                    'variant_id' => 99,
                    'name' => 'Amplifier Roland Cube',
                    'sku' => 'AMP-RLD-01',
                    'barcode' => '7788991122',
                    'price' => 2500000,
                    'qty' => 1,
                ],
            ],
        ];

        $encodedData = base64_encode(json_encode($payload));
        $response = $this->actingAs($user)->get(route('pos.barcode-print.sheet', ['data' => $encodedData]));

        $response->assertStatus(200);
        $response->assertSee('Amplifier Roland Cube');
    }

    public function test_can_handle_large_barcode_queue_without_url_length_issues(): void
    {
        $user = User::factory()->create();

        $queue = [];
        for ($i = 1; $i <= 500; $i++) {
            $queue[] = [
                'variant_id' => $i,
                'name' => "Produk Barcode Uji Coba Ke-{$i}",
                'sku' => "SKU-TEST-" . str_pad($i, 5, '0', STR_PAD_LEFT),
                'barcode' => "89900000" . str_pad($i, 5, '0', STR_PAD_LEFT),
                'price' => 100000 + ($i * 1000),
                'qty' => 1,
            ];
        }

        $payload = [
            'layout' => '3col',
            'queue' => $queue,
        ];

        $token = ProductHelper::storeBarcodePrintPayload($payload);
        $this->assertNotEmpty($token);

        // URL is concise (less than 100 characters)
        $url = route('pos.barcode-print.sheet', ['token' => $token]);
        $this->assertLessThan(120, strlen($url));

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);
        $response->assertSee('Produk Barcode Uji Coba Ke-1');
        $response->assertSee('Produk Barcode Uji Coba Ke-500');
    }
}
