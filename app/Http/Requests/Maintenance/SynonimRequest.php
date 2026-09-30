<?php

namespace App\Http\Requests\Maintenance;

use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\Synonim;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SynonimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperadmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rm_code' => strtoupper(trim((string) $this->input('rm_code'))),
            'fg_code' => strtoupper(trim((string) $this->input('fg_code'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'rm_code' => [
                'required',
                'string',
                Rule::exists(RawMaterial::class, 'code'),
                $this->uniqueRawMaterialRule(),
            ],
            'fg_code' => ['required', 'string', Rule::exists(FinishedGood::class, 'code')],
        ];
    }

    private function uniqueRawMaterialRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $rawMaterialId = RawMaterial::where('code', $value)->value('id');

            if ($rawMaterialId === null) {
                return;
            }

            $query = Synonim::where('raw_material_id', $rawMaterialId);
            $synonim = $this->route('synonim');

            if ($synonim instanceof Synonim) {
                $query->where('id', '!=', $synonim->getKey());
            }

            if ($query->exists()) {
                $fail(__('Raw Material tersebut sudah memiliki synonim.'));
            }
        };
    }
}
