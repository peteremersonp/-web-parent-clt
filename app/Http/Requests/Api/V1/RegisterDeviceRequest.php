<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDeviceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'machine_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'os_version' => ['nullable', 'string', 'max:64'],
        ];
    }
}
