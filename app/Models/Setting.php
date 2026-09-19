<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const KEY_POLL_INTERVAL_SEC = 'poll_interval_sec';

    public const KEY_DNS_MODE = 'dns_mode';

    public const KEY_UPSTREAM_DOH = 'upstream_doh';

    public const KEY_FALLBACK_DNS = 'fallback_dns';

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function upsertValue(string $key, mixed $value): Setting
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->save();

        return $setting;
    }
}
