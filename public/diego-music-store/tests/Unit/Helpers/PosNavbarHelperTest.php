<?php

namespace Tests\Unit\Helpers;

use App\Helpers\PosNavbarHelper;
use Tests\TestCase;

class PosNavbarHelperTest extends TestCase
{
    /** @test */
    public function it_returns_null_when_active_session_info_is_null_and_user_unauthenticated(): void
    {
        $session = PosNavbarHelper::resolveActiveSession(null);
        $this->assertNull($session);
    }

    /** @test */
    public function it_normalizes_active_session_info_defaults_when_keys_are_missing(): void
    {
        $input = [
            'id' => 42,
        ];

        $normalized = PosNavbarHelper::resolveActiveSession($input);

        $this->assertIsArray($normalized);
        $this->assertEquals(42, $normalized['id']);
        $this->assertEquals('Kasir', $normalized['opened_by']);
        $this->assertEquals(0, $normalized['opening_cash']);
        $this->assertNotEmpty($normalized['opened_at']);
    }

    /** @test */
    public function it_preserves_provided_values_in_active_session_info(): void
    {
        $input = [
            'id'           => 99,
            'opened_by'    => 'Budi Kasir',
            'opening_cash' => 250000,
            'opened_at'    => '16 Sep 2026 08:00',
        ];

        $normalized = PosNavbarHelper::resolveActiveSession($input);

        $this->assertEquals(99, $normalized['id']);
        $this->assertEquals('Budi Kasir', $normalized['opened_by']);
        $this->assertEquals(250000, $normalized['opening_cash']);
        $this->assertEquals('16 Sep 2026 08:00', $normalized['opened_at']);
    }

    /** @test */
    public function it_provides_all_expected_context_keys_in_get_context(): void
    {
        $context = PosNavbarHelper::getContext(null, '/custom-back-url');

        $expectedKeys = [
            'resolvedBackUrl',
            'activeSessionInfo',
            'isOwner',
            'currentActiveBranchId',
            'currentBranchModel',
            'userBranchList',
            'currentEmployee',
            'todayAttendance',
            'usedOffDays',
            'quotaOffDays',
            'isOverQuota',
            'overCount',
            'todayStatusText',
            'clockState',
            'clockInTimeText',
            'clockOutTimeText',
            'allBranchEmployees',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $context, "Missing context key: {$key}");
        }

        $this->assertEquals('/custom-back-url', $context['resolvedBackUrl']);
        $this->assertEquals('not_clocked_in', $context['clockState']);
        $this->assertEquals('Belum Presensi', $context['todayStatusText']);
    }
}
