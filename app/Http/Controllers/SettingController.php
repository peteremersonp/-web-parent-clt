<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\PolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            Setting::KEY_POLL_INTERVAL_SEC => (int) Setting::value(Setting::KEY_POLL_INTERVAL_SEC, 600),
            Setting::KEY_DNS_MODE => Setting::value(Setting::KEY_DNS_MODE, 'local-filter'),
            Setting::KEY_UPSTREAM_DOH => Setting::value(Setting::KEY_UPSTREAM_DOH, 'https://cloudflare-dns.com/dns-query'),
            Setting::KEY_FALLBACK_DNS => Setting::value(Setting::KEY_FALLBACK_DNS, '1.1.1.1'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request, PolicyService $policy): RedirectResponse
    {
        $data = $request->validate([
            'poll_interval_sec' => ['required', 'integer', 'min:30', 'max:86400'],
            'dns_mode' => ['required', 'in:local-filter,off'],
            'upstream_doh' => ['nullable', 'url', 'max:255'],
            'fallback_dns' => ['nullable', 'ip', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::upsertValue($key, $value === '' ? null : $value);
        }

        Log::info('settings.updated', ['settings' => $data]);

        $policy->bumpAllDevices();

        return back()->with('status', 'Configuración guardada. Todos los dispositivos recibirán la nueva política en su próximo poll.');
    }
}
