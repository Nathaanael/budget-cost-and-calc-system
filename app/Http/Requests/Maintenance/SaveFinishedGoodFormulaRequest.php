<?php

namespace App\Http\Requests\Maintenance;

use App\Models\RawMaterial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFinishedGoodFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $rows = collect($this->input('rows', []))
            ->map(function ($row) {
                if (! is_array($row)) {
                    return $row;
                }

                $row['code'] = strtoupper(trim((string) ($row['code'] ?? '')));

                return $row;
            })
            ->all();

        $this->merge(['rows' => $rows]);
    }

    public function rules(): array
    {
        return [
            'rows' => ['present', 'array', 'max:500'],
            'rows.*.id' => ['nullable', 'integer', Rule::exists('finished_good_formula_items', 'id')],
            'rows.*.code' => [
                'required',
                'string',
                'distinct:ignore_case',
                Rule::exists(RawMaterial::class, 'code'),
            ],
            'rows.*.standard' => ['required', 'numeric', 'min:0', 'max:999999999999.999999'],
            'rows.*.deleted' => ['sometimes', 'boolean'],
        ];
    }
}
