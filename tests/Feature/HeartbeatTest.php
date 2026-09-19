<?php

namespace Tests\Feature;

use App\Models\Device;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeartbeatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_heartbeat_updates_device_state(): void
    {
        $device = Device::factory()->create(['status' => Device::STATUS_NEVER]);

        $payload = [
            'status' => 'online',
            'applied_version' => 3,
            'errors' => ['DNS port 53 in use'],
            'uptime_sec' => 86400,
            'applied_at' => '2026-09-18T10:00:00Z',
        ];

        $this->withToken($device->api_token)
            ->postJson('/api/v1/heartbeat', $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'status' => 'online',
            'applied_version' => 3,
        ]);

        $fresh = $device->fresh();
        $this->assertNotNull($fresh->last_seen_at);
        $this->assertSame('online', $fresh->last_heartbeat['status']);
        $this->assertSame($payload, $fresh->last_heartbeat);
    }

    public function test_heartbeat_with_bad_token_returns_401(): void
    {
        $this->postJson('/api/v1/heartbeat', [
            'status' => 'online',
            'applied_version' => 1,
        ])->assertUnauthorized()->assertJson(['message' => 'unauthorized']);
    }

    public function test_heartbeat_invalid_payload_returns_422(): void
    {
        $device = Device::factory()->create();

        $this->withToken($device->api_token)
            ->postJson('/api/v1/heartbeat', [
                'status' => 'unknown',
                'applied_version' => -5,
                'errors' => 'not-an-array',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['status']]);
    }
}
