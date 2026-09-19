<?php

namespace Database\Seeders;

use App\Models\BlacklistProfile;
use App\Models\BlacklistProfileAllow;
use App\Models\BlacklistProfileRule;
use App\Models\ScheduleWindow;
use Illuminate\Database\Seeder;

/**
 * Configuración de la familia: perfiles de bloqueo + programación semanal.
 * Idempotente (firstOrCreate) y reemplaza cualquier programación anterior.
 *
 * Jornadas:
 *   L-V  00:00-05:00  sin-internet (block-all)
 *   L-V  05:00-14:00  lista-escolar (allow-only: IA + Google + LMS + MinEducación)
 *   L-V  14:00-19:00  jornada-domestica (normal excepto redes sociales/mensajería)
 *   L-V  19:00-22:00  lista-nocturna (allow-only: emergencia)
 *   L-V  22:00-24:00  sin-internet
 *   Sáb  00:00-07:00  sin-internet | 07:00-20:00 jornada-domestica | 20:00-24:00 sin-internet
 *   Dom  00:00-07:00  sin-internet | 07:00-19:00 jornada-domestica | 19:00-24:00 sin-internet
 */
class ParentScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = [
            'libre' => ['desc' => 'Navegación sin límites (no se usará de momento)', 'mode' => 'off', 'rules' => [], 'allows' => []],
            'sin-internet' => ['desc' => 'Ninguna navegación permitida (horario nocturno)', 'mode' => 'block-all', 'rules' => [], 'allows' => []],
            'lista-escolar' => ['desc' => 'Jornada Escolar: IA, Google, LMS y Ministerio de Educación', 'mode' => 'allow-only',
                'rules' => [],
                'allows' => [
                    'google.com', '*.google.com',
                    'chatgpt.com', '*.chatgpt.com', 'openai.com', '*.openai.com',
                    'claude.ai', '*.claude.ai', 'anthropic.com', '*.anthropic.com',
                    'perplexity.ai', '*.perplexity.ai', 'copilot.microsoft.com',
                    'chat.mistral.ai', 'mistral.ai', '*.mistral.ai',
                    'chat.deepseek.com', 'deepseek.com', '*.deepseek.com',
                    'grok.com', '*.grok.com',
                    'wikipedia.org', '*.wikipedia.org',
                    'khanacademy.org', '*.khanacademy.org',
                    'duolingo.com', '*.duolingo.com',
                    'moodle.org', '*.moodle.org', 'teams.microsoft.com', 'zoom.us', '*.zoom.us',
                    'mineducacion.gov.co', '*.mineducacion.gov.co',
                    'colombiaaprende.edu.co', '*.colombiaaprende.edu.co',
                ]],
            'lista-nocturna' => ['desc' => 'Solo contacto de emergencia (noches)', 'mode' => 'allow-only',
                'rules' => [],
                'allows' => ['google.com', '*.google.com', 'wikipedia.org', '*.wikipedia.org']],
            'jornada-domestica' => ['desc' => 'Navegación normal excepto redes sociales y mensajería', 'mode' => 'local-filter',
                'rules' => [
                    ['instagram.com', 'exact'], ['*.instagram.com', 'wildcard'],
                    ['tiktok.com', 'exact'], ['*.tiktok.com', 'wildcard'],
                    ['facebook.com', 'exact'], ['*.facebook.com', 'wildcard'], ['fbcdn.net', 'exact'], ['*.fbcdn.net', 'wildcard'],
                    ['messenger.com', 'exact'], ['*.messenger.com', 'wildcard'],
                    ['x.com', 'exact'], ['*.x.com', 'wildcard'], ['twitter.com', 'exact'], ['*.twitter.com', 'wildcard'],
                    ['snapchat.com', 'exact'], ['*.snapchat.com', 'wildcard'],
                    ['reddit.com', 'exact'], ['*.reddit.com', 'wildcard'],
                    ['twitch.tv', 'exact'], ['*.twitch.tv', 'wildcard'],
                    ['discord.com', 'exact'], ['*.discord.com', 'wildcard'],
                    ['whatsapp.com', 'exact'], ['*.whatsapp.com', 'wildcard'], ['wa.me', 'exact'],
                    ['telegram.org', 'exact'], ['*.telegram.org', 'wildcard'],
                    ['youtube.com', 'exact'], ['*.youtube.com', 'wildcard'], ['ytimg.com', 'exact'], ['*.ytimg.com', 'wildcard'],
                ],
                'allows' => []],
        ];

        // Reemplaza cualquier programación/perfil anterior (la config de la familia es la fuente de verdad).
        ScheduleWindow::query()->delete();
        BlacklistProfile::query()->whereNotIn('slug', array_keys($perfiles))->get()->each(fn ($p) => $p->delete());

        $P = [];
        foreach ($perfiles as $slug => $def) {
            $p = BlacklistProfile::firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'description' => $def['desc'], 'dns_mode' => $def['mode'], 'enabled' => true]
            );
            $p->update(['description' => $def['desc'], 'dns_mode' => $def['mode'], 'enabled' => true]);
            foreach ($def['rules'] as [$domain, $type]) {
                BlacklistProfileRule::firstOrCreate(
                    ['blacklist_profile_id' => $p->id, 'domain' => $domain, 'type' => $type],
                    ['enabled' => true]
                );
            }
            foreach ($def['allows'] as $domain) {
                BlacklistProfileAllow::firstOrCreate(
                    ['blacklist_profile_id' => $p->id, 'domain' => $domain],
                    ['enabled' => true]
                );
            }
            $P[$slug] = $p;
        }

        $windows = [];
        foreach ([1, 2, 3, 4, 5] as $d) {
            $windows[] = ['sin-internet', $d, '00:00', '05:00'];
            $windows[] = ['lista-escolar', $d, '05:00', '14:00'];
            $windows[] = ['jornada-domestica', $d, '14:00', '19:00'];
            $windows[] = ['lista-nocturna', $d, '19:00', '22:00'];
            $windows[] = ['sin-internet', $d, '22:00', '23:59'];
        }
        foreach ([['sin-internet', '00:00', '07:00'], ['jornada-domestica', '07:00', '20:00'], ['sin-internet', '20:00', '23:59']] as [$slug, $s, $e]) {
            $windows[] = [$slug, 6, $s, $e];
        }
        foreach ([['sin-internet', '00:00', '07:00'], ['jornada-domestica', '07:00', '19:00'], ['sin-internet', '19:00', '23:59']] as [$slug, $s, $e]) {
            $windows[] = [$slug, 0, $s, $e];
        }

        foreach ($windows as [$slug, $d, $s, $e]) {
            ScheduleWindow::firstOrCreate(
                ['profile_id' => $P[$slug]->id, 'day_of_week' => $d, 'start_time' => $s, 'end_time' => $e],
                ['enabled' => true]
            );
        }
    }
}
