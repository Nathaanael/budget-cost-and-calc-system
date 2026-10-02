<?php

namespace App\Http\Requests\Calculate;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    public function rules(): array
    {
        return [
            'reference_id' => ['required', 'integer', 'exists:references,id'],
        ];
    }
}
