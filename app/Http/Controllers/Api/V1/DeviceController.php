<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterDeviceRequest;
use App\Models\Device;
use App\Services\PolicyService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    public function register(RegisterDeviceRequest $request, PolicyService $policy): JsonResponse
    {
        if (Device::where('machine_id', $request->input('machine_id'))->exists()) {
            return $this->conflict();
        }

        $device = new Device([
            'machine_id' => $request->input('machine_id'),
            'name' => $request->input('name'),
            'os_version' => $request->input('os_version'),
            'api_token' => Device::generateToken(),
            'policy_version' => 1,
            'status' => Device::STATUS_NEVER,
        ]);

        try {
            $device->save();
        } catch (QueryException) {
            // Race: two simultaneous registrations with the same machine_id.
            return $this->conflict();
        }

        Log::info('device.registered', [
            'device_id' => $device->id,
            'machine_id' => $device->machine_id,
            'name' => $device->name,
            'os_version' => $device->os_version,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'device' => [
                'machine_id' => $device->machine_id,
                'name' => $device->name,
            ],
            'token' => $device->api_token,
            'policy' => $policy->buildForDevice($device),
        ], 200);
    }

    private function conflict(): JsonResponse
    {
        return response()->json(['message' => 'already-registered'], 409)
            ->header('X-Hint', 'use /api/v1/policy/{machine_id} with your existing token');
    }
}
