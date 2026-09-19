<?php

namespace Database\Seeders;

use App\Models\BlacklistProfile;
use App\Models\BlacklistProfileRule;
use App\Models\ScheduleWindow;
use Illuminate\Database\Seeder;

/**
 * Perfiles de bloqueo por defecto + programación semanal de ejemplo.
 * Idempotente: se puede ejecutar varias veces sin duplicar (firstOrCreate).
 */
class ParentScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $estudio = $this->profile('estudio', 'Bloquea distracciones para dedicarse a estudiar', [
            ['domain' => 'instagram.com', 'type' => 'exact'],
            ['domain' => 'tiktok.com', 'type' => 'exact'],
            ['domain' => 'youtube.com', 'type' => 'exact'],
            ['domain' => 'twitch.tv', 'type' => 'exact'],
            ['domain' => 'netflix.com', 'type' => 'exact'],
            ['domain' => 'discord.com', 'type' => 'exact'],
        ]);

        $entretenimiento = $this->profile('entretenimiento', 'Entretenimiento moderado', [
            ['domain' => 'roblox.com', 'type' => 'exact'],
            ['domain' => 'minecraft.net', 'type' => 'exact'],
        ]);

        $sabados = $this->profile('sabados', 'Lista especial para sábados', [
            ['domain' => 'youtube.com', 'type' => 'exact'],
            ['domain' => 'twitch.tv', 'type' => 'exact'],
        ]);

        $domingos = $this->profile('domingos', 'Lista especial para domingos', [
            ['domain' => 'netflix.com', 'type' => 'exact'],
            ['domain' => 'max.com', 'type' => 'exact'],
        ]);

        // dormir = bloquea TODO el internet (NXDOMAIN a cualquier consulta)
        $dormir = $this->profile('dormir', 'Bloquea todo el internet', [], 'block-all');

        // castigo = bloqueo total instantáneo; se asigna por franja u override cuando se necesite
        $this->profile('castigo', 'Bloqueo total por castigo', [], 'block-all');

        // Programación por defecto (aplica a todos los dispositivos salvo override).
        // day_of_week: 0=Domingo .. 6=Sábado.
        $this->window($estudio, 1, '07:00', '16:00');   // lunes a viernes 7am-4pm
        $this->window($estudio, 2, '07:00', '16:00');
        $this->window($estudio, 3, '07:00', '16:00');
        $this->window($estudio, 4, '07:00', '16:00');
        $this->window($estudio, 5, '07:00', '16:00');

        $this->window($entretenimiento, 1, '16:00', '17:00'); // 4pm-5pm
        $this->window($entretenimiento, 2, '16:00', '17:00');
        $this->window($entretenimiento, 3, '16:00', '17:00');
        $this->window($entretenimiento, 4, '16:00', '17:00');
        $this->window($entretenimiento, 5, '16:00', '17:00');

        $this->window($estudio, 1, '18:00', '21:00');   // 6pm-9pm estudio
        $this->window($estudio, 2, '18:00', '21:00');
        $this->window($estudio, 3, '18:00', '21:00');
        $this->window($estudio, 4, '18:00', '21:00');
        $this->window($estudio, 5, '18:00', '21:00');

        // dormir: 10pm-23:59 y 00:00-05:00 (todos los días)
        for ($d = 0; $d <= 6; $d++) {
            $this->window($dormir, $d, '22:00', '23:59');
            $this->window($dormir, $d, '00:00', '05:00');
        }
    }

    private function profile(string $name, string $description, array $rules, string $dnsMode = BlacklistProfile::DNS_MODE_LOCAL_FILTER): BlacklistProfile
    {
        $profile = BlacklistProfile::firstOrCreate(
            ['slug' => BlacklistProfile::slugify($name)],
            ['name' => $name, 'description' => $description, 'dns_mode' => $dnsMode, 'enabled' => true],
        );

        foreach ($rules as $rule) {
            BlacklistProfileRule::firstOrCreate([
                'blacklist_profile_id' => $profile->id,
                'domain' => $rule['domain'],
                'type' => $rule['type'],
            ], ['enabled' => true]);
        }

        return $profile;
    }

    private function window(BlacklistProfile $profile, int $day, string $start, string $end): void
    {
        ScheduleWindow::firstOrCreate([
            'profile_id' => $profile->id,
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
        ], ['enabled' => true]);
    }
}
