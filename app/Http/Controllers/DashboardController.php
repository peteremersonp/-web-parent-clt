<?php

namespace App\Http\Controllers;

use App\Models\BlacklistRule;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $total = Device::count();

        $freshSince = now()->subMinutes(5);

        $counts = [
            'total' => $total,
            'online' => Device::whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', $freshSince)
                ->where('status', '!=', Device::STATUS_NEVER)
                ->count(),
            'degraded' => Device::where('status', Device::STATUS_DEGRADED)->count(),
            'offline' => Device::where('status', Device::STATUS_OFFLINE)->count(),
            'never' => Device::where('status', Device::STATUS_NEVER)->count(),
        ];

        $topRules = BlacklistRule::query()
            ->where('enabled', true)
            ->select('domain', DB::raw('count(*) as total'), DB::raw('count(device_id) as device_scoped'))
            ->groupBy('domain')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('dashboard', compact('counts', 'topRules'));
    }
}
