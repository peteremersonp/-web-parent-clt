<?php

namespace Tests\Feature;

use App\Models\BlacklistRule;
use App\Models\Device;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_policy_returns_global_and_device_rules(): void
    {
        $device = Device::factory()->create(['machine_id' => '7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c']);

        BlacklistRule::factory()->create([
            'device_id' => null,
            'domain' => 'facebook.com',
            'type' => 'exact',
        ]);
        BlacklistRule::factory()->create([
            'device_id' => null,
            'domain' => '*.tiktok.com',
            'type' => 'wildcard',
        ]);
        BlacklistRule::factory()->create([
            'device_id' => $device->id,
            'domain' => 'youtube.com',
            'type' => 'exact',
        ]);
        // Disabled rules must not appear.
        BlacklistRule::factory()->create([
            'device_id' => null,
            'domain' => 'disabled.com',
            'type' => 'exact',
            'enabled' => false,
        ]);

        $response = $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$device->machine_id}");

        $response->assertOk()
            ->assertJsonCount(3, 'blacklist')
            ->assertJson([
                'version' => 1,
                'dns' => ['mode' => 'local-filter'],
                'settings' => ['poll_interval_sec' => 600],
            ]);

        $domains = collect($response->json('blacklist'))->pluck('domain')->sort()->values()->all();
        $this->assertEquals(
            ['*.tiktok.com', 'facebook.com', 'youtube.com'],
            $domains
        );
    }

    public function test_policy_returns_304_when_if_none_match_matches(): void
    {
        $device = Device::factory()->create();

        $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$device->machine_id}", ['If-None-Match' => '"1"'])
            ->assertStatus(304);

        Device::query()->increment('policy_version');

        $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$device->machine_id}", ['If-None-Match' => '"1"'])
            ->assertOk()
            ->assertJsonPath('version', 2);
    }

    public function test_policy_with_bad_token_returns_401(): void
    {
        $device = Device::factory()->create();

        $this->withToken('deadbeefdeadbeef')
            ->getJson("/api/v1/policy/{$device->machine_id}")
            ->assertUnauthorized()
            ->assertJson(['message' => 'unauthorized']);
    }

    public function test_policy_token_mismatch_returns_401(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();

        $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$other->machine_id}")
            ->assertUnauthorized();
    }

    public function test_policy_unknown_machine_id_returns_404(): void
    {
        $device = Device::factory()->create();

        $this->withToken($device->api_token)
            ->getJson('/api/v1/policy/00000000-0000-4000-8000-000000000000')
            ->assertNotFound()
            ->assertJson(['message' => 'device not found']);
    }
}
