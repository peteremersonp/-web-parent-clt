<?php

namespace App\Services;

use App\Models\BlacklistProfile;
use App\Models\BlacklistProfileRule;
use App\Models\BlacklistRule;
use App\Models\Device;
use App\Models\ScheduleOverride;
use App\Models\ScheduleWindow;
use App\Models\Setting;
use Illuminate\Support\Collection;

class PolicyService
{
    public const DNS_MODES = ['local-filter', 'off', 'block-all'];

    public const DEFAULT_SCHEDULE_EVAL_SEC = 30;

    /**
     * Rebuild the policy JSON for a device from scratch.
     *
     * Cuando el dispositivo tiene programación (schedule), el contrato incluye la
     * lista completa de ventanas (día + horario + perfil con su blacklist y modo DNS).
     * El agente evalúa localmente con su reloj qué ventana está activa y aplica ese
     * perfil; si no hay ventana activa y hay schedule, aplica modo "off" (DNS original).
     * Sin schedule, se conserva el comportamiento heredado (dns.mode + blacklist global).
     */
    public function buildForDevice(Device $device): array
    {
        $rules = BlacklistRule::query()
            ->where('enabled', true)
            ->where(fn ($q) => $q->whereNull('device_id')->orWhere('device_id', $device->id))
            ->orderBy('domain')
            ->get(['domain', 'type']);

        return [
            'version' => $device->policy_version,
            'dns' => [
                'mode' => $this->dnsMode(),
            ],
            'blacklist' => $rules->map(fn (BlacklistRule $r) => [
                'domain' => $r->domain,
                'type' => $r->type,
            ])->values()->all(),
            'schedule' => $this->effectiveWindowsFor($device)->values()->all(),
            'settings' => [
                'poll_interval_sec' => (int) Setting::value(Setting::KEY_POLL_INTERVAL_SEC, 600),
                'schedule_eval_sec' => self::DEFAULT_SCHEDULE_EVAL_SEC,
            ],
        ];
    }

    /**
     * Ventanas efectivas del dispositivo: programación por defecto menos los overrides
     * 'delete' y con los overrides 'replace' resueltos a su perfil.
     *
     * @return Collection<int, array{day_of_week: int, start_time: string, end_time: string, profile: array}>
     */
    public function effectiveWindowsFor(Device $device): Collection
    {
        $overrides = ScheduleOverride::query()
            ->where('device_id', $device->id)
            ->get()
            ->keyBy('window_id');

        return ScheduleWindow::query()
            ->where('enabled', true)
            ->with(['profile.rules' => fn ($q) => $q->where('enabled', true)->orderBy('domain')])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->reject(fn (ScheduleWindow $w) => $overrides->has($w->id)
                && $overrides->get($w->id)->action === ScheduleOverride::ACTION_DELETE)
            ->map(fn (ScheduleWindow $w) => $this->formatWindow(
                $w->day_of_week,
                (string) $w->start_time,
                (string) $w->end_time,
                $overrides->get($w->id)?->action === ScheduleOverride::ACTION_REPLACE && $overrides->get($w->id)->profile
                    ? $overrides->get($w->id)->profile
                    : $w->profile,
            ))
            ->values();
    }

    private function formatWindow(int $dayOfWeek, string $start, string $end, BlacklistProfile $profile): array
    {
        return [
            'day_of_week' => $dayOfWeek,
            'start_time' => substr($start, 0, 5),
            'end_time' => substr($end, 0, 5),
            'profile' => [
                'name' => $profile->name,
                'dns_mode' => $profile->dns_mode,
                'blacklist' => $profile->rules
                    ->filter(fn (BlacklistProfileRule $r) => $r->enabled)
                    ->map(fn (BlacklistProfileRule $r) => [
                        'domain' => $r->domain,
                        'type' => $r->type,
                    ])->values()->all(),
            ],
        ];
    }

    public function dnsMode(): string
    {
        $mode = Setting::value(Setting::KEY_DNS_MODE, 'local-filter');

        return in_array($mode, self::DNS_MODES, true) ? $mode : 'local-filter';
    }

    /**
     * Bump the policy version on every device. Call it on any change that
     * affects what agents receive (rules globales/por device, settings DNS,
     * perfiles o programación).
     */
    public function bumpAllDevices(): void
    {
        Device::query()->increment('policy_version');
    }
}
