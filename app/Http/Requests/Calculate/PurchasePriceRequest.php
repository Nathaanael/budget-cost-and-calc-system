<?php

namespace App\Http\Requests\Calculate;

use App\Support\PlantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchasePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return optional($this->user())->isSuperadmin() === true;
    }

    public function rules(): array
    {
        return [
            'reference_id' => ['required', 'integer', 'exists:references,id'],
            'calculate_all' => ['sometimes', 'boolean'],
            'raw_material_ids' => ['exclude_if:calculate_all,true', 'required', 'array', 'min:1'],
            'raw_material_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('raw_materials', 'id')
                    ->where('plant_id', app(PlantContext::class)->id())
                    ->where('currency_type', 'USD'),
            ],
        ];
    }
}
