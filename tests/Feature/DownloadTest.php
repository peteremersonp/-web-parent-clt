<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/downloads/setup')->assertRedirect('/login');
    }

    public function test_admin_can_download_setup(): void
    {
        $user = User::query()->where('email', 'admin@parentclt.local')->firstOrFail();

        $response = $this->actingAs($user)->get('/downloads/setup');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/octet-stream');

        $file = $response->getFile();
        $this->assertNotNull($file, 'La respuesta debe contener un BinaryFileResponse con el exe');
        $this->assertStringStartsWith('MZ', (string) file_get_contents($file->getPathname(), length: 2), 'El instalador debe ser un exe de Windows (MZ header)');
    }
}
