<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = [
        'code',
        'description_1',
        'description_2',
        'period',
        'period_description',
        'rate_current',
        'rate_le',
        'rate_1',
        'rate_2',
        'rate_3',
        'rate_4',
        'pe_ckp_current',
        'pe_ckp_le',
        'pe_ckp_1',
        'pe_ckp_2',
        'pe_ckp_3',
        'pe_ckp_4',
        'pe_smg_current',
        'pe_smg_le',
        'pe_smg_1',
        'pe_smg_2',
        'pe_smg_3',
        'pe_smg_4',
        'pe_sby_current',
        'pe_sby_le',
        'pe_sby_1',
        'pe_sby_2',
        'pe_sby_3',
        'pe_sby_4',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return collect($this->numericFields())
            ->mapWithKeys(fn ($field) => [$field => 'decimal:2'])
            ->all();
    }

    public static function numericFields(): array
    {
        $fields = ['rate_current', 'rate_le', 'rate_1', 'rate_2', 'rate_3', 'rate_4'];

        foreach (['ckp', 'smg', 'sby'] as $plant) {
            foreach (['current', 'le', '1', '2', '3', '4'] as $period) {
                $fields[] = "pe_{$plant}_{$period}";
            }
        }

        return $fields;
    }
}
