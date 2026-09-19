<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlacklistProfileRule extends Model
{
    public const TYPE_EXACT = 'exact';

    public const TYPE_WILDCARD = 'wildcard';

    protected $fillable = ['blacklist_profile_id', 'domain', 'type', 'enabled'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BlacklistProfile::class, 'blacklist_profile_id');
    }
}
