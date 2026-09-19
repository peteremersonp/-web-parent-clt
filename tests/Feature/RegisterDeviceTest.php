<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterDeviceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_register_returns_token_and_initial_policy(): void
    {
        $response = $this->postJson('/api/v1/devices/register', [
            'machine_id' => '7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c',
            'name' => 'PC-Sala',
            'os_version' => '10.0.19045',
        ]);

        $response->assertOk()
            ->assertJsonPath('device.machine_id', '7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c')
            ->assertJsonPath('device.name', 'PC-Sala')
            ->assertJsonPath('policy.version', 1)
            ->assertJsonPath('policy.dns.mode', 'local-filter')
            ->assertJsonPath('policy.settings.poll_interval_sec', 600)
            ->assertJsonCount(0, 'policy.blacklist');

        $token = $response->json('token');
        $this->assertTrue(ctype_xdigit($token) && strlen($token) === 80);

        $this->assertDatabaseHas('devices', [
            'machine_id' => '7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c',
            'status' => 'never',
        ]);
    }

    public function test_register_duplicate_machine_id_returns_409(): void
    {
        $payload = [
            'machine_id' => '7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c',
            'name' => 'PC-Sala',
        ];

        $this->postJson('/api/v1/devices/register', $payload)->assertOk();
        $this->postJson('/api/v1/devices/register', $payload)
            ->assertStatus(409)
            ->assertHeader('X-Hint', 'use /api/v1/policy/{machine_id} with your existing token')
            ->assertJson([
                'message' => 'already-registered',
            ]);
    }

    public function test_register_with_invalid_payload_returns_422(): void
    {
        $this->postJson('/api/v1/devices/register', [
            'machine_id' => 'not-a-uuid',
            'name' => '',
        ])->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['machine_id', 'name']]);
    }
}
