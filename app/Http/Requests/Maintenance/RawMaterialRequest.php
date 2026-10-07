<?php

namespace App\Http\Requests\Maintenance;

use App\Models\RawMaterial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RawMaterialRequest extends FormRequest
{
    private const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $materialId = strtoupper(trim((string) $this->material_id));

        $this->merge([
            'code' => strtoupper(trim((string) $this->code)),
            'material_id' => $materialId !== '' ? $materialId : null,
        ]);
    }

    public function rules(): array
    {
        $rawMaterial = $this->route('rawMaterial');

        $rules = [
            'code' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($rawMaterial) {
                    if ($rawMaterial instanceof RawMaterial && $value === $rawMaterial->code) {
                        return;
                    }

                    if (! preg_match('/^[A-Z0-9]{1,7}$/', (string) $value)) {
                        $fail(__('Code RM hanya boleh berisi huruf dan angka dengan maksimal 7 karakter.'));
                    }
                },
                Rule::unique(RawMaterial::class)->ignore($rawMaterial),
            ],
            'material_id' => ['nullable', 'string', 'max:50', Rule::unique(RawMaterial::class, 'material_id')->ignore($rawMaterial)],
            'description' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'wastage_all' => ['required', 'numeric', 'min:0', 'max:999999.9999'],
            'currency_type' => ['required', Rule::in(['Rp', 'USD'])],
            'type_rm' => ['nullable', 'string', 'max:50'],
        ];

        foreach (self::PERIODS as $period) {
            $rules["usd_{$period}"] = ['nullable', 'numeric', 'min:0', 'max:999999999.99'];
            $rules["rupiah_{$period}"] = ['nullable', 'numeric', 'min:0', 'max:999999999.99'];
        }

        return $rules;
    }
}
