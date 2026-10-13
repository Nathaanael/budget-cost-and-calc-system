<?php

namespace App\Http\Requests\Calculate;

use App\Support\PlantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitCostBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return optional($this->user())->isSuperadmin() === true;
    }

    public function rules(): array
    {
        $multiLevel = $this->input('stage') === 'multi_level';

        return [
            'stage' => ['required', Rule::in(['multi_level', 'regular'])],
            // Batas atas longgar untuk stage multi_level yang dikirim sekaligus.
            'finished_good_ids' => ['required', 'array', 'min:1', 'max:1000'],
            'finished_good_ids.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('finished_goods', 'id')->where(function ($query) use ($multiLevel) {
                    $query->where('plant_id', app(PlantContext::class)->id());
                    $multiLevel
                        ? $query->where('multi_level', 'Y')
                        : $query->where('multi_level', '!=', 'Y');
                }),
            ],
        ];
    }
}
