<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleWindow extends Model
{
    protected $fillable = ['profile_id', 'day_of_week', 'start_time', 'end_time', 'enabled'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BlacklistProfile::class, 'profile_id');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(ScheduleOverride::class, 'window_id');
    }

    public static function dayName(int $dayOfWeek): string
    {
        // 0=Domingo ... 6=Sábado (compatible con .NET DayOfWeek)
        return ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'][$dayOfWeek];
    }
}
