<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_panel_requires_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_logout(): void
    {
        $this->post('/login', [
            'email' => 'admin@parentclt.local',
            'password' => env('ADMIN_PASSWORD', 'ChangeMe_2026!'),
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_rejects_bad_credentials(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'admin@parentclt.local',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_seeder_creates_single_admin_with_env_password(): void
    {
        $admin = User::where('email', 'admin@parentclt.local')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check(env('ADMIN_PASSWORD', 'ChangeMe_2026!'), $admin->password));
    }
}
