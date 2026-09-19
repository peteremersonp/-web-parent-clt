<?php

use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\HeartbeatController;
use App\Http\Controllers\Api\V1\PolicyController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('devices/register', [DeviceController::class, 'register']);

    Route::middleware('auth.device-token')->group(function () {
        Route::get('policy/{machineId}', [PolicyController::class, 'show']);
        Route::post('heartbeat', [HeartbeatController::class, 'store']);
    });
});
