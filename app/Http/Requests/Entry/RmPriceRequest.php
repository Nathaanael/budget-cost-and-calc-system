<?php

namespace App\Http\Requests\Entry;

use App\Models\RawMaterialPrice;
use App\Support\PlantContext;
use Illuminate\Foundation\Http\FormRequest;

class RmPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    public function rules(): array
    {
        $rules = [
            'raw_material_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('raw_materials', 'id')->where('plant_id', app(PlantContext::class)->id()),
            ],
        ];

        foreach (RawMaterialPrice::PERIODS as $period) {
            $rules["usd_{$period}"] = ['required', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2'];
            $rules["rupiah_{$period}"] = ['required', 'numeric', 'min:0', 'max:9999999.99', 'decimal:0,2'];
        }

        return $rules;
    }
}
