<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeartbeatRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:online,degraded,offline'],
            'applied_version' => ['required', 'integer', 'min:0'],
            'errors' => ['nullable', 'array', 'max:20'],
            'errors.*' => ['string'],
            'uptime_sec' => ['nullable', 'integer', 'min:0'],
            'applied_at' => ['nullable', 'date'],
        ];
    }
}
