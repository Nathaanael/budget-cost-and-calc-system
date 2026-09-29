<?php

namespace App\Http\Requests\Maintenance;

use App\Models\Noodle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoodleRequest extends FormRequest
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
        $noodle = $this->route('noodle');

        return [
            'code' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($noodle) {
                    if ($noodle instanceof Noodle && $value === $noodle->code) {
                        return;
                    }

                    if (! preg_match('/^\d{6,30}$/', (string) $value)) {
                        $fail(__('Noodle Code hanya boleh berisi angka dengan minimal 6 digit.'));
                    }
                },
                Rule::unique(Noodle::class)->ignore($noodle),
            ],
            'description' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
        ];
    }

}
