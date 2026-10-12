<?php

namespace App\Http\Requests\Maintenance;

use App\Models\RawMaterial;
use App\Support\PlantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RawMaterialRequest extends FormRequest
{
    private const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    public function authorize(): bool
    {
        return optional($this->user())->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $materialId = strtoupper(trim((string) $this->material_id));

        $normalizedPrices = [];

        foreach (self::PERIODS as $period) {
            $field = "rupiah_{$period}";
            $normalizedPrices[$field] = $this->normalizePrice($this->input($field));
        }

        $this->merge(array_merge([
            'code' => strtoupper(trim((string) $this->code)),
            'material_id' => $materialId !== '' ? $materialId : null,
        ], $normalizedPrices));
    }

    public function rules(): array
    {
        $rawMaterial = $this->route('rawMaterial');
        $plantId = app(PlantContext::class)->id();

        $rules = [
            'code' => [
                'required',
                'string',
                function (string $attribute, $value, \Closure $fail) use ($rawMaterial) {
                    if ($rawMaterial instanceof RawMaterial && $value === $rawMaterial->code) {
                        return;
                    }

                    if (! preg_match('/^[A-Z0-9]{1,7}$/', (string) $value)) {
                        $fail(__('Code RM hanya boleh berisi huruf dan angka dengan maksimal 7 karakter.'));
                    }
                },
                Rule::unique(RawMaterial::class)->where('plant_id', $plantId)->ignore($rawMaterial),
            ],
            'material_id' => ['nullable', 'string', 'max:50'],
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

    private function normalizePrice($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = preg_replace('/\s+/', '', trim($value));

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, ',')) {
            return str_replace(',', '.', str_replace('.', '', $normalized));
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $normalized) === 1) {
            return str_replace('.', '', $normalized);
        }

        return $normalized;
    }
}
