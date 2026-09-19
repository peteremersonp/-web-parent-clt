<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\PolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = Device::withCount('blacklistRules')->orderByDesc('last_seen_at')->get();

        return view('devices.index', compact('devices'));
    }

    public function show(Device $device, PolicyService $policy): View
    {
        $device->load('blacklistRules');
        $lastPolicy = $policy->buildForDevice($device);

        return view('devices.show', compact('device', 'lastPolicy'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $device->update($data);

        return back()->with('status', 'Dispositivo renombrado.');
    }

    public function regenerateToken(Device $device): RedirectResponse
    {
        $device->forceFill(['api_token' => Device::generateToken()])->save();

        Log::info('device.token.regenerated', [
            'device_id' => $device->id,
            'machine_id' => $device->machine_id,
        ]);

        return back()->with('status', 'Token regenerado.')
            ->with('new_token', $device->fresh()->api_token);
    }

    public function destroy(Device $device): RedirectResponse
    {
        Log::info('device.deleted', [
            'device_id' => $device->id,
            'machine_id' => $device->machine_id,
        ]);

        $device->delete();

        return redirect()->route('devices.index')->with('status', 'Dispositivo eliminado.');
    }
}
