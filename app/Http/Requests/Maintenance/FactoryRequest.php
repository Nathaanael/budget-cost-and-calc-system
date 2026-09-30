<?php

namespace App\Http\Requests\Maintenance;

use App\Models\AreaNoodle;
use App\Models\Factory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FactoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'code' => strtoupper(trim((string) $this->input('code'))),
            'description' => trim((string) $this->input('description')),
        ];

        foreach (range(1, 10) as $position) {
            $value = strtoupper(trim((string) $this->input("area_{$position}")));
            $data["area_{$position}"] = $value === '' ? null : $value;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $factory = $this->route('factory');

        $rules = [
            'code' => ['required', 'string', 'max:2', Rule::unique(Factory::class)->ignore($factory)],
            'description' => ['required', 'string', 'max:20'],
        ];

        foreach (range(1, 10) as $position) {
            $rules["area_{$position}"] = [
                'nullable',
                'string',
                Rule::exists(AreaNoodle::class, 'code'),
            ];
        }

        return $rules;
    }
}
