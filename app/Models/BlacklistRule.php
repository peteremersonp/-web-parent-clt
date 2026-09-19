<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlacklistRule extends Model
{
    use HasFactory;

    public const TYPE_EXACT = 'exact';

    public const TYPE_WILDCARD = 'wildcard';

    protected $fillable = [
        'device_id',
        'domain',
        'type',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function isGlobal(): bool
    {
        return $this->device_id === null;
    }
}
