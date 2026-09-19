<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

if (! function_exists('auditLogAsJson')) {
    function auditLogAsJson(string $event, Model $model): void
    {
        Log::info($event, ['model' => $model]);
    }
}
