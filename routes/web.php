<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceScheduleController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// PWA: página offline precacheada por el service worker
Route::view('/offline', 'pwa.offline')->name('pwa.offline');

// Iconos PWA servidos por Laravel: inmune a la config de nginx/apache del hosting
// (y a /icons/ reservado por mod_alias de Apache). Solo nombres de archivo PNG.
Route::get('/pwa-icons/{file}', function (string $file) {
    if (! preg_match('/^[\w.-]+\.png$/', $file)) {
        abort(404);
    }
    $path = public_path('pwa-icons/'.$file);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('file', '[\w.-]+\.png');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    // El URI es "callbak" (con esa ortografía) porque así está registrado en Google Cloud Console.
    Route::get('/google/callbak', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/downloads/setup', [DownloadController::class, 'setup'])->name('downloads.setup');

    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
    Route::patch('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
    Route::post('/devices/{device}/token', [DeviceController::class, 'regenerateToken'])->name('devices.token');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    Route::get('/devices/{device}/schedule', [DeviceScheduleController::class, 'show'])->name('devices.schedule');
    Route::post('/devices/{device}/schedule/{window}', [DeviceScheduleController::class, 'storeOverride'])->name('devices.schedule.override');
    Route::delete('/devices/{device}/schedule/{window}', [DeviceScheduleController::class, 'destroyOverride'])->name('devices.schedule.override.destroy');

    Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
    Route::post('/rules', [RuleController::class, 'store'])->name('rules.store');
    Route::post('/rules/{rule}/toggle', [RuleController::class, 'toggle'])->name('rules.toggle');
    Route::delete('/rules/{rule}', [RuleController::class, 'destroy'])->name('rules.destroy');

    Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
    Route::post('/profiles', [ProfileController::class, 'store'])->name('profiles.store');
    Route::get('/profiles/{profile}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::patch('/profiles/{profile}', [ProfileController::class, 'update'])->name('profiles.update');
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy'])->name('profiles.destroy');
    Route::post('/profiles/{profile}/rules', [ProfileController::class, 'storeRule'])->name('profiles.rules.store');
    Route::post('/profiles/{profile}/rules/{rule}/toggle', [ProfileController::class, 'toggleRule'])->name('profiles.rules.toggle');
    Route::delete('/profiles/{profile}/rules/{rule}', [ProfileController::class, 'destroyRule'])->name('profiles.rules.destroy');
    Route::post('/profiles/{profile}/allows', [ProfileController::class, 'storeAllow'])->name('profiles.allows.store');
    Route::post('/profiles/{profile}/allows/{allow}/toggle', [ProfileController::class, 'toggleAllow'])->name('profiles.allows.toggle');
    Route::delete('/profiles/{profile}/allows/{allow}', [ProfileController::class, 'destroyAllow'])->name('profiles.allows.destroy');

    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/{window}/toggle', [ScheduleController::class, 'toggle'])->name('schedules.toggle');
    Route::delete('/schedules/{window}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
