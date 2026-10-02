<?php

namespace App\Http\Requests\Entry;

use App\Models\VolumeNoodle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VolumeNoodleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $values = [];

        foreach ([...VolumeNoodle::LE_FIELDS, ...VolumeNoodle::MONTH_FIELDS] as $field) {
            $values[$field] = $this->input($field, 0);
        }

        $this->merge($values);
    }

    public function rules(): array
    {
        $volumeNoodle = $this->route('volumeNoodle');
        $uniquePair = Rule::unique('volume_noodles', 'area_noodle_id')
            ->where(fn ($query) => $query->where('noodle_id', $this->integer('noodle_id')))
            ->ignore($volumeNoodle);

        $rules = [
            'area_noodle_id' => ['required', 'integer', 'exists:area_noodles,id', $uniquePair],
            'noodle_id' => ['required', 'integer', 'exists:noodles,id'],
        ];

        foreach ([...VolumeNoodle::LE_FIELDS, ...VolumeNoodle::MONTH_FIELDS] as $field) {
            $rules[$field] = ['required', 'numeric', 'min:0', 'max:9999999999999999.99', 'decimal:0,2'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'area_noodle_id.unique' => __('Kombinasi Area Code dan Noodle Code sudah terdaftar.'),
        ];
    }
}
