<?php

namespace Tests\Feature\Actions;

use App\Actions\Accounting\CreateAsset;
use App\Actions\Accounting\PostAssetDisposal;
use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDisposalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Account $assetAccount;
    protected Account $accumAccount;
    protected Account $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name'       => 'Cabang Utama',
            'store_name' => 'Diego Music Store',
            'address'    => 'Jl. Musik No. 123',
            'phone'      => '081234567890',
            'is_active'  => true,
        ]);

        $this->user = User::factory()->create([
            'name'      => 'Admin Akuntansi',
            'username'  => 'adminakuntansi',
            'email'     => 'accounting@diegomusic.com',
            'is_active' => true,
        ]);

        $this->assetAccount = Account::create([
            'code'           => '1201',
            'name'           => 'Peralatan Audio & Sound System',
            'classification' => 'Asset',
            'is_active'      => true,
            'is_header'     => false,
        ]);

        $this->accumAccount = Account::create([
            'code'           => '1291',
            'name'           => 'Akumulasi Penyusutan Peralatan',
            'classification' => 'Asset',
            'is_active'      => true,
            'is_header'     => false,
        ]);

        $this->cashAccount = Account::create([
            'code'           => '1101',
            'name'           => 'Kas Utama',
            'classification' => 'Asset',
            'is_active'      => true,
            'is_header'     => false,
        ]);
    }

    /** @test */
    public function it_can_create_a_fixed_asset_record()
    {
        $action = new CreateAsset();
        $asset = $action->execute([
            'name'                     => 'Sound System Mixer Allen & Heath SQ-5',
            'category'                 => 'Peralatan Audio & Sound',
            'branch_id'                => $this->branch->id,
            'purchase_date'            => '2025-01-10',
            'purchase_cost'            => 45000000,
            'salvage_value'            => 5000000,
            'useful_life_years'        => 5,
            'accumulated_depreciation' => 15000000,
            'notes'                    => 'Unit demo toko utama',
        ]);

        $this->assertDatabaseHas('assets', [
            'id'            => $asset->id,
            'name'          => 'Sound System Mixer Allen & Heath SQ-5',
            'purchase_cost' => 45000000,
            'status'        => 'active',
        ]);

        $this->assertEquals(30000000, $asset->book_value); // 45m - 15m
    }

    /** @test */
    public function it_can_post_asset_sale_disposal_and_create_journal_entry()
    {
        $asset = Asset::create([
            'asset_code'               => 'AST-202607-0001',
            'name'                     => 'Komputer POS Kasir i5',
            'category'                 => 'Elektronik & POS',
            'branch_id'                => $this->branch->id,
            'purchase_date'            => '2024-05-01',
            'purchase_cost'            => 10000000,
            'salvage_value'            => 1000000,
            'useful_life_years'        => 3,
            'accumulated_depreciation' => 6000000, // Book value = 4.000.000
            'status'                   => 'active',
        ]);

        // Sold for 4.500.000 => Gain = +500.000
        $disposal = AssetDisposal::create([
            'disposal_number' => 'DSP-202607-0001',
            'disposal_date'   => now()->toDateString(),
            'asset_id'        => $asset->id,
            'branch_id'       => $this->branch->id,
            'disposal_type'   => 'sale',
            'book_value'      => 4000000,
            'disposal_amount' => 4500000,
            'gain_loss_amount'=> 500000,
            'account_id'      => $this->cashAccount->id,
            'status'          => 'draft',
        ]);

        $postAction = new PostAssetDisposal();
        $postedDisposal = $postAction->execute($disposal);

        $this->assertEquals('posted', $postedDisposal->status);
        $this->assertNotNull($postedDisposal->journal_entry_id);

        $asset->refresh();
        $this->assertEquals('disposed', $asset->status);

        $this->assertDatabaseHas('journal_entries', [
            'id'        => $postedDisposal->journal_entry_id,
            'entry_no'  => 'DSP-202607-0001',
            'status'    => 'posted',
        ]);
    }

    /** @test */
    public function it_can_post_asset_write_off_disposal()
    {
        $asset = Asset::create([
            'asset_code'               => 'AST-202607-0002',
            'name'                     => 'Etalase Display Kaca (Rusak)',
            'category'                 => 'Bangunan & Display',
            'branch_id'                => $this->branch->id,
            'purchase_date'            => '2023-01-01',
            'purchase_cost'            => 8000000,
            'accumulated_depreciation' => 5000000, // Book value = 3.000.000
            'status'                   => 'active',
        ]);

        $disposal = AssetDisposal::create([
            'disposal_number' => 'DSP-202607-0002',
            'disposal_date'   => now()->toDateString(),
            'asset_id'        => $asset->id,
            'branch_id'       => $this->branch->id,
            'disposal_type'   => 'write_off',
            'book_value'      => 3000000,
            'disposal_amount' => 0,
            'gain_loss_amount'=> -3000000,
            'status'          => 'draft',
            'notes'           => 'Pecah karena musibah banjir',
        ]);

        $postAction = new PostAssetDisposal();
        $postedDisposal = $postAction->execute($disposal);

        $this->assertEquals('posted', $postedDisposal->status);

        $asset->refresh();
        $this->assertEquals('written_off', $asset->status);
    }
}
