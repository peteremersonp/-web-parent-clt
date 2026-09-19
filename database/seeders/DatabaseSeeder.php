<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@parentclt.local')],
            [
                'name' => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe_2026!')),
            ],
        );

        $seedSettings = [
            Setting::KEY_POLL_INTERVAL_SEC => 600,
            Setting::KEY_DNS_MODE => 'local-filter',
            Setting::KEY_UPSTREAM_DOH => 'https://cloudflare-dns.com/dns-query',
            Setting::KEY_FALLBACK_DNS => '1.1.1.1',
        ];

        foreach ($seedSettings as $key => $value) {
            Setting::upsertValue($key, $value);
        }
    }
}
