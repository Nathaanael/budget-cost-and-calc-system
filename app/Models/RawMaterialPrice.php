<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterialPrice extends Model
{
    public const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    protected $fillable = [
        'raw_material_id',
        'period',
        'usd_amount',
        'rupiah_amount',
        'source_kind',
        'reference_id',
        'exchange_rate',
        'calculated_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'usd_amount' => 'float',
            'rupiah_amount' => 'float',
            'exchange_rate' => 'float',
            'calculated_at' => 'datetime',
        ];
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
