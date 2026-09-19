<?php

namespace Tests\Feature;

use App\Models\BlacklistRule;
use App\Models\Device;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RulesPerDeviceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'admin@parentclt.local')->first());
    }

    public function test_device_rule_only_appears_for_that_device(): void
    {
        $deviceA = Device::factory()->create();
        $deviceB = Device::factory()->create();

        $this->post('/rules', [
            'device_id' => $deviceA->id,
            'domain' => '*.tiktok.com',
            'type' => 'wildcard',
        ])->assertRedirect();

        $this->withToken($deviceA->api_token)
            ->getJson("/api/v1/policy/{$deviceA->machine_id}")
            ->assertOk()
            ->assertJsonCount(1, 'blacklist')
            ->assertJsonFragment(['domain' => '*.tiktok.com', 'type' => 'wildcard']);

        $this->withToken($deviceB->api_token)
            ->getJson("/api/v1/policy/{$deviceB->machine_id}")
            ->assertOk()
            ->assertJsonCount(0, 'blacklist');
    }

    public function test_deleting_a_device_deletes_its_rules(): void
    {
        $device = Device::factory()->create();
        BlacklistRule::factory()->create(['device_id' => $device->id, 'domain' => 'youtube.com']);

        $this->delete("/devices/{$device->id}")->assertRedirect('/devices');

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
        $this->assertDatabaseMissing('blacklist_rules', ['device_id' => $device->id]);
    }
}
