<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleOverride extends Model
{
    public const ACTION_DELETE = 'delete';

    public const ACTION_REPLACE = 'replace';

    protected $fillable = ['device_id', 'window_id', 'action', 'profile_id'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function window(): BelongsTo
    {
        return $this->belongsTo(ScheduleWindow::class, 'window_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BlacklistProfile::class, 'profile_id');
    }
}
