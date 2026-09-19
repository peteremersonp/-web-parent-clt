<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlacklistProfileAllow extends Model
{
    protected $fillable = ['blacklist_profile_id', 'domain', 'enabled'];

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
