<?php

namespace App\Http\Requests\Maintenance;

use App\Models\FinishedGood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinishedGoodRequest extends FormRequest
{
    private const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    private const FACTORIES = ['semarang', 'surabaya', 'palembang'];

    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [
            'code' => strtoupper(trim((string) $this->code)),
            'multi_level' => strtoupper((string) $this->input('multi_level', 'N')),
            'active' => strtoupper((string) $this->input('active', 'Y')),
            'selling_price' => $this->normalizePrice($this->input('selling_price')),
        ];

        foreach (self::PERIODS as $period) {
            $normalized["unit_cost_{$period}"] = $this->normalizePrice($this->input("unit_cost_{$period}"));
            $normalized["unit_price_{$period}"] = $this->normalizePrice($this->input("unit_price_{$period}"));

            foreach (self::FACTORIES as $factory) {
                $field = "unit_price_{$factory}_{$period}";
                $normalized[$field] = $this->normalizePrice($this->input($field));
            }
        }

        foreach (['cikampek', ...self::FACTORIES] as $factory) {
            $field = "pe_{$factory}";
            $normalized[$field] = $this->normalizePrice($this->input($field));
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $finishedGood = $this->route('finishedGood');
        $rules = [
            'code' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($finishedGood) {
                    if ($finishedGood instanceof FinishedGood && $value === $finishedGood->code) {
                        return;
                    }

                    if (! preg_match('/^[A-Z0-9]{1,7}$/', (string) $value)) {
                        $fail(__('Code FG hanya boleh berisi huruf dan angka dengan maksimal 7 karakter.'));
                    }
                },
                Rule::unique(FinishedGood::class)->ignore($finishedGood),
            ],
            'description' => ['required', 'string', 'max:150'],
            'description_1' => ['nullable', 'string', 'max:150'],
            'product_type_1' => ['required', 'integer', 'min:0'],
            'product_type_2' => ['required', 'integer', 'min:0'],
            'batch' => ['nullable', 'integer', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'multi_level' => ['required', Rule::in(['Y', 'N'])],
            'active' => ['required', Rule::in(['Y', 'N'])],
        ];

        foreach (self::PERIODS as $period) {
            $rules["unit_cost_{$period}"] = ['nullable', 'numeric', 'min:0'];
            $rules["unit_price_{$period}"] = ['nullable', 'numeric', 'min:0'];

            foreach (self::FACTORIES as $factory) {
                $rules["unit_price_{$factory}_{$period}"] = ['nullable', 'numeric', 'min:0'];
            }
        }

        foreach (['cikampek', ...self::FACTORIES] as $factory) {
            $rules["pe_{$factory}"] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    private function normalizePrice(mixed $value): mixed
    {
        return is_string($value) ? str_replace('.', '', $value) : $value;
    }
}
