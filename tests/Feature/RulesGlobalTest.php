<?php

namespace Tests\Feature;

use App\Models\BlacklistRule;
use App\Models\Device;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RulesGlobalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function actingAsAdmin(): User
    {
        $admin = User::where('email', 'admin@parentclt.local')->first()
            ?? User::factory()->create();
        $this->actingAs($admin);

        return $admin;
    }

    public function test_create_rule_bumps_policy_version_and_appears_in_policy(): void
    {
        $device = Device::factory()->create(['policy_version' => 1]);
        $this->actingAsAdmin();

        $this->post('/rules', [
            'domain' => 'Facebook.com',
            'type' => 'exact',
        ])->assertRedirect();
        $this->assertDatabaseHas('blacklist_rules', [
            'device_id' => null,
            'domain' => 'facebook.com',
            'type' => 'exact',
            'enabled' => 1,
        ]);

        $this->assertSame(2, $device->fresh()->policy_version);

        $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$device->machine_id}")
            ->assertOk()
            ->assertJsonPath('version', 2)
            ->assertJsonFragment([
                'domain' => 'facebook.com',
                'type' => 'exact',
            ]);
    }

    public function test_toggle_rule_bumps_policy_version(): void
    {
        $device = Device::factory()->create(['policy_version' => 1]);
        $rule = BlacklistRule::factory()->create([
            'device_id' => null,
            'domain' => 'facebook.com',
            'type' => 'exact',
            'enabled' => true,
        ]);
        $this->actingAsAdmin();

        $this->post("/rules/{$rule->id}/toggle")->assertRedirect();

        $this->assertSame(2, $device->fresh()->policy_version);

        $this->withToken($device->api_token)
            ->getJson("/api/v1/policy/{$device->machine_id}")
            ->assertOk()
            ->assertJsonCount(0, 'blacklist');
    }

    public function test_delete_rule_bumps_policy_version(): void
    {
        $device = Device::factory()->create(['policy_version' => 1]);
        $rule = BlacklistRule::factory()->create([
            'device_id' => null,
            'domain' => 'facebook.com',
            'type' => 'exact',
            'enabled' => true,
        ]);
        $this->actingAsAdmin();

        $this->delete("/rules/{$rule->id}")->assertRedirect();

        $this->assertDatabaseMissing('blacklist_rules', ['id' => $rule->id]);
        $this->assertSame(2, $device->fresh()->policy_version);
    }

    public function test_duplicate_global_rule_rejected(): void
    {
        BlacklistRule::factory()->create([
            'device_id' => null,
            'domain' => 'facebook.com',
            'type' => 'exact',
        ]);
        $this->actingAsAdmin();

        $this->post('/rules', [
            'domain' => 'facebook.com',
            'type' => 'exact',
        ])->assertSessionHasErrors('domain');
    }
}
