<?php

namespace App\Http\Controllers;

use App\Models\BlacklistProfile;
use App\Models\ScheduleWindow;
use App\Services\PolicyService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $windows = ScheduleWindow::query()
            ->with(['profile', 'overrides'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $profiles = BlacklistProfile::query()->orderBy('name')->get();

        return view('schedules.index', compact('windows', 'profiles'));
    }

    public function store(Request $request, PolicyService $policy): RedirectResponse
    {
        $data = $request->validate([
            'profile_id' => 'required|exists:blacklist_profiles,id',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $created = 0;
        foreach ($data['days'] as $day) {
            $existing = ScheduleWindow::query()
                ->where('profile_id', $data['profile_id'])
                ->where('day_of_week', $day)
                ->where('start_time', $data['start_time'])
                ->where('end_time', $data['end_time'])
                ->exists();
            if (! $existing) {
                ScheduleWindow::create([
                    'profile_id' => $data['profile_id'],
                    'day_of_week' => $day,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'enabled' => true,
                ]);
                $created++;
            }
        }

        if ($created > 0) {
            $policy->bumpAllDevices();
        }
        auditLogAsJson('schedule.window.created', ['days' => $data['days'], 'slot' => $data['start_time'].'-'.$data['end_time']]);

        return back()->with('status', $created > 0
            ? "Ventana(s) creada(s) para {$created} día(s)."
            : 'Esas franjas ya existían.');
    }

    public function toggle(ScheduleWindow $window, PolicyService $policy): RedirectResponse
    {
        $window->update(['enabled' => ! $window->enabled]);
        $policy->bumpAllDevices();
        auditLogAsJson('schedule.window.toggled', $window);

        return back()->with('status', 'Ventana '.($window->enabled ? 'activada' : 'desactivada').'.');
    }

    public function destroy(ScheduleWindow $window, PolicyService $policy): RedirectResponse
    {
        auditLogAsJson('schedule.window.deleted', $window);
        $window->delete();
        $policy->bumpAllDevices();

        return back()->with('status', 'Ventana eliminada.');
    }
}
