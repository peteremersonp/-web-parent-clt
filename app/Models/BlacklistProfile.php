<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlacklistProfile extends Model
{
    public const DNS_MODE_LOCAL_FILTER = 'local-filter';

    public const DNS_MODE_OFF = 'off';

    public const DNS_MODE_BLOCK_ALL = 'block-all';

    public const DNS_MODE_ALLOW_ONLY = 'allow-only';

    protected $fillable = ['name', 'slug', 'description', 'dns_mode', 'enabled'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(BlacklistProfileRule::class, 'blacklist_profile_id');
    }

    public function allows(): HasMany
    {
        return $this->hasMany(BlacklistProfileAllow::class, 'blacklist_profile_id');
    }

    public function windows(): HasMany
    {
        return $this->hasMany(ScheduleWindow::class, 'profile_id');
    }

    public static function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'perfil';
    }
}
