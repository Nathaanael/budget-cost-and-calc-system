<?php

namespace App\Http\Requests\Maintenance;

use App\Models\AreaNoodle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaNoodleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->code))]);
    }

    public function rules(): array
    {
        $areaNoodle = $this->route('areaNoodle');

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique(AreaNoodle::class)->ignore($areaNoodle)],
            'description' => ['required', 'string', 'max:150'],
        ];
    }
}
