<?php

namespace App\Http\Requests\Calculate;

use App\Support\PlantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitCostPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    public function rules(): array
    {
        return [
            'calculate_multi_level' => ['required', 'boolean'],
            'calculate_all' => ['sometimes', 'boolean'],
            'finished_good_ids' => ['exclude_if:calculate_all,true', 'required', 'array', 'min:1'],
            'finished_good_ids.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('finished_goods', 'id')->where(function ($query) {
                    $query->where('plant_id', app(PlantContext::class)->id());
                    if (! $this->boolean('calculate_multi_level')) {
                        $query->where('multi_level', '!=', 'Y');
                    }
                }),
            ],
        ];
    }
}
