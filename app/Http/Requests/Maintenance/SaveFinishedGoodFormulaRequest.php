<?php

namespace App\Http\Requests\Maintenance;

use App\Models\RawMaterial;
use App\Support\PlantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFinishedGoodFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return optional($this->user())->isSuperadmin() === true;
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
                Rule::exists(RawMaterial::class, 'code')->where('plant_id', app(PlantContext::class)->id()),
            ],
            // Standard Unit dihitung server dari Standard Batch / Batch, bukan input user.
            'rows.*.standard_batch' => ['required', 'numeric', 'min:0', 'max:999999999999.9999999999'],
            'rows.*.deleted' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
