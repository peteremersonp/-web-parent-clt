<?php

namespace Tests\Feature;

use App\Models\BlacklistProfile;
use App\Models\Device;
use App\Models\ScheduleWindow;
use App\Services\PolicyService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllowOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_allow_only_profile_exposes_allowlist_in_policy(): void
    {
        $profile = BlacklistProfile::create([
            'name' => 'solo-colegio',
            'slug' => 'solo-colegio',
            'dns_mode' => 'allow-only',
            'enabled' => true,
        ]);
        $profile->allows()->createMany([
            ['domain' => 'colegiodominio.com', 'enabled' => true],
            ['domain' => 'gobierno.com', 'enabled' => true],
            ['domain' => 'deshabilitado.com', 'enabled' => false],
        ]);

        ScheduleWindow::create([
            'profile_id' => $profile->id,
            'day_of_week' => 1,
            'start_time' => '07:00',
            'end_time' => '16:00',
            'enabled' => true,
        ]);

        $device = Device::create([
            'machine_id' => '11111111-2222-3333-4444-555555555555',
            'name' => 'PC-Test',
            'api_token' => Device::generateToken(),
            'policy_version' => 1,
            'status' => Device::STATUS_NEVER,
        ]);

        $policy = (new PolicyService)->buildForDevice($device);

        $window = collect($policy['schedule'])->first(
            fn ($w) => $w['profile']['name'] === 'solo-colegio'
        );

        $this->assertNotNull($window, 'La ventana allow-only debe estar en el schedule');
        $this->assertSame('allow-only', $window['profile']['dns_mode']);

        $allowed = collect($window['profile']['allowlist'])->pluck('domain')->all();
        $this->assertContains('colegiodominio.com', $allowed);
        $this->assertContains('gobierno.com', $allowed);
        $this->assertNotContains('deshabilitado.com', $allowed, 'Los permitidos inactivos no viajan');
    }
}
