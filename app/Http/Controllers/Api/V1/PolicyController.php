<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\PolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PolicyController extends Controller
{
    public function show(Request $request, string $machineId, PolicyService $policy): JsonResponse
    {
        /** @var Device $authDevice */
        $authDevice = $request->attributes->get('device');

        $device = Device::where('machine_id', $machineId)->first();
        if (! $device) {
            return response()->json(['message' => 'device not found'], 404);
        }

        if ($device->id !== $authDevice->id) {
            return response()->json(['message' => 'unauthorized'], 401);
        }

        $etag = '"'.$device->policy_version.'"';
        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, Response::HTTP_NOT_MODIFIED);
        }

        return response()->json($policy->buildForDevice($device), 200);
    }
}
