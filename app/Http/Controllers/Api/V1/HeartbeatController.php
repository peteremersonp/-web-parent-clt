<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreHeartbeatRequest;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HeartbeatController extends Controller
{
    public function store(StoreHeartbeatRequest $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('device');

        $payload = $request->only(['status', 'applied_version', 'errors', 'uptime_sec', 'applied_at']);

        $device->forceFill([
            'status' => $request->input('status'),
            'last_seen_at' => now(),
            'applied_version' => $request->input('applied_version'),
            'last_heartbeat' => $payload,
        ])->save();

        $errors = $request->input('errors', []);
        if (is_array($errors) && count($errors) > 0) {
            Log::warning('device.heartbeat.errors', [
                'device_id' => $device->id,
                'machine_id' => $device->machine_id,
                'errors' => $errors,
            ]);
        }

        return response()->json(['ok' => true], 200);
    }
}
