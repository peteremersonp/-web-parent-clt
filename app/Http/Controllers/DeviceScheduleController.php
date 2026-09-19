<?php

namespace App\Http\Controllers;

use App\Models\BlacklistProfile;
use App\Models\Device;
use App\Models\ScheduleOverride;
use App\Models\ScheduleWindow;
use App\Services\PolicyService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeviceScheduleController extends Controller
{
    public function show(Device $device, PolicyService $policy): View
    {
        $windows = ScheduleWindow::query()
            ->with(['profile', 'overrides' => fn ($q) => $q->where('device_id', $device->id)])
            ->where('enabled', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $profiles = BlacklistProfile::query()->orderBy('name')->get();
        $effective = $policy->effectiveWindowsFor($device);

        return view('devices.schedule', compact('device', 'windows', 'profiles', 'effective'));
    }

    public function storeOverride(Request $request, Device $device, ScheduleWindow $window, PolicyService $policy): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:delete,replace',
            'profile_id' => 'nullable|exists:blacklist_profiles,id|required_if:action,replace',
        ]);

        ScheduleOverride::updateOrCreate(
            ['device_id' => $device->id, 'window_id' => $window->id],
            [
                'action' => $data['action'],
                'profile_id' => $data['action'] === ScheduleOverride::ACTION_REPLACE
                    ? $data['profile_id']
                    : null,
            ],
        );

        $policy->bumpAllDevices();
        auditLogAsJson('schedule.override.saved', [
            'device_id' => $device->id,
            'window_id' => $window->id,
            'action' => $data['action'],
        ]);

        return back()->with('status', 'Override guardado para este dispositivo.');
    }

    public function destroyOverride(Device $device, ScheduleWindow $window, PolicyService $policy): RedirectResponse
    {
        ScheduleOverride::query()
            ->where('device_id', $device->id)
            ->where('window_id', $window->id)
            ->delete();

        $policy->bumpAllDevices();
        auditLogAsJson('schedule.override.deleted', [
            'device_id' => $device->id,
            'window_id' => $window->id,
        ]);

        return back()->with('status', 'Override eliminado: la franja por defecto vuelve a aplicar.');
    }
}
