<?php

namespace App\Http\Requests\Maintenance;

use App\Models\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'code' => strtoupper(trim((string) $this->input('code'))),
            'description_1' => trim((string) $this->input('description_1')),
            'description_2' => trim((string) $this->input('description_2')),
            'period' => trim((string) $this->input('period')),
            'period_description' => trim((string) $this->input('period_description')),
        ];

        foreach (Reference::numericFields() as $field) {
            $value = trim((string) $this->input($field, '0'));
            $data[$field] = $value === '' ? '0' : $this->normalizeNumber($value, str_starts_with($field, 'rate_'));
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $reference = $this->route('reference');

        $rules = [
            'code' => ['required', 'string', 'max:2', Rule::unique(Reference::class)->ignore($reference)],
            'description_1' => ['required', 'string', 'max:30'],
            'description_2' => ['required', 'string', 'max:30'],
            'period' => ['required', 'string', 'max:8'],
            'period_description' => ['required', 'string', 'max:15'],
        ];

        foreach (Reference::numericFields() as $field) {
            $rules[$field] = ['required', 'numeric', 'min:0', str_starts_with($field, 'rate_') ? 'max:9999999999999.99' : 'max:99999999.99'];
        }

        return $rules;
    }

    private function normalizeNumber(string $value, bool $wholeNumberDisplay): string
    {
        if ($wholeNumberDisplay) {
            if (preg_match('/^\d+\.\d{2}$/', $value)) {
                return $value;
            }

            return preg_replace('/[^0-9]/', '', $value) ?: '0';
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            return str_replace(',', '.', str_replace('.', '', $value));
        }

        return str_replace(',', '.', $value);
    }
}
