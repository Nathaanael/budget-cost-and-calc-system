<?php

namespace App\Http\Requests\Calculate;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
