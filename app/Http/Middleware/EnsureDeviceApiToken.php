<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');
        $token = str_starts_with($header, 'Bearer ')
            ? trim(substr($header, 7))
            : null;

        $device = $token ? Device::where('api_token', $token)->first() : null;

        if (! $device) {
            return response()->json(['message' => 'unauthorized'], 401);
        }

        $request->attributes->set('device', $device);

        return $next($request);
    }
}
