<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_api_without_token_returns_401_json(): void
    {
        $this->getJson('/api/v1/policy/7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c')
            ->assertStatus(401)
            ->assertJson(['message' => 'unauthorized']);
    }
}
