<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'admin@parentclt.local')->first());
    }

    public function test_update_settings_bumps_policy_version_and_applies_to_policy(): void
    {
        $device = Device::factory()->create(['policy_version' => 1]);

        $this->post('/settings', [
            'poll_interval_sec' => 240,
            'dns_mode' => 'off',
            'upstream_doh' => '',      // nullable
            'fallback_dns' => '8.8.8.8',
        ])->assertRedirect();

        $saved = Setting::where('key', Setting::KEY_POLL_INTERVAL_SEC)->first()->value;
        $this->assertTrue($saved === 240 || (is_array($saved) && in_array(240, $saved, true)));
        $this->assertSame('off', Setting::value(Setting::KEY_DNS_MODE));

        $this->assertSame(2, $device->fresh()->policy_version);

        $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$device->machine_id}")
            ->assertOk()
            ->assertJsonPath('version', 2)
            ->assertJsonPath('dns.mode', 'off')
            ->assertJsonPath('settings.poll_interval_sec', 240);
    }

    public function test_update_settings_invalid_values_returns_422(): void
    {
        $this->post('/settings', [
            'poll_interval_sec' => 10,     // below min 30
            'dns_mode' => 'bogus',
            'upstream_doh' => 'nope',
            'fallback_dns' => 'not-an-ip',
        ])->assertSessionHasErrors(['poll_interval_sec', 'dns_mode', 'upstream_doh', 'fallback_dns']);

        $this->assertSame(600, Setting::value(Setting::KEY_POLL_INTERVAL_SEC));
        $this->assertSame('local-filter', Setting::value(Setting::KEY_DNS_MODE));
    }
}
