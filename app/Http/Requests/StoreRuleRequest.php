<?php

namespace App\Http\Requests;

use App\Models\BlacklistRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRuleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'device_id' => ['nullable', 'integer', 'exists:devices,id'],
            'domain' => ['required', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    $value = $this->normalizeDomain($value);
                    if ($this->input('type') === 'wildcard') {
                        if (! preg_match('/^\*\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*$/i', $value)) {
                            $fail('El dominio wildcard debe tener el formato *.ejemplo.com.');
                        }
                    } elseif (! preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*$/i', $value)) {
                        $fail('Formato de dominio inválido.');
                    }
                },
            ],
            'type' => ['required', 'string', Rule::in([BlacklistRule::TYPE_EXACT, BlacklistRule::TYPE_WILDCARD])],
        ];
    }

    /**
     * SQLite ignores NULL in unique indexes, so duplicate detection for
     * global rules must live in the application layer.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $query = BlacklistRule::query()
                ->where('domain', $this->normalizeDomain($this->input('domain')))
                ->where('type', $this->input('type'));

            if (empty($this->input('device_id'))) {
                $query->whereNull('device_id');
            } else {
                $query->where('device_id', $this->input('device_id'));
            }

            if ($query->exists()) {
                $validator->errors()->add('domain', 'Esta regla ya existe.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'domain' => $this->normalizeDomain($this->input('domain')),
            'type' => $this->input('type', BlacklistRule::TYPE_EXACT),
        ]);
    }

    private function normalizeDomain(?string $domain): ?string
    {
        if ($domain === null) {
            return null;
        }

        $domain = strtolower(trim($domain));
        $domain = rtrim($domain, '.');

        return $domain === '' ? null : $domain;
    }
}
