<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    public const STATUS_NEVER = 'never';

    public const STATUS_ONLINE = 'online';

    public const STATUS_DEGRADED = 'degraded';

    public const STATUS_OFFLINE = 'offline';

    protected $fillable = [
        'machine_id',
        'name',
        'os_version',
        'api_token',
        'policy_version',
        'status',
        'last_seen_at',
        'applied_version',
        'last_heartbeat',
    ];

    protected function casts(): array
    {
        return [
            'policy_version' => 'integer',
            'last_seen_at' => 'datetime',
            'applied_version' => 'integer',
            'last_heartbeat' => 'array',
        ];
    }

    public function blacklistRules(): HasMany
    {
        return $this->hasMany(BlacklistRule::class);
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(40));
    }
}
