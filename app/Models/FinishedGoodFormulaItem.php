<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinishedGoodFormulaItem extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = [
        'finished_good_formula_id',
        'raw_material_id',
        'standard_batch',
        'standard',
        'position',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'finished_good_formula_id' => 'integer',
        'raw_material_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'standard_batch' => 'decimal:10',
        'standard' => 'decimal:10',
        'position' => 'integer',
    ];

    /**
     * Standard Unit = Standard Batch / Batch FG.
     * Packaging (RM memiliki ID/material_id): ROUND(x / batch; 8).
     * RM (tanpa ID): ROUND(x / batch * 1000; 5).
     */
    public static function calculateStandardUnit($standardBatch, int $batch, bool $isPackaging): float
    {
        if ($batch <= 0) {
            return 0.0;
        }

        $value = (float) $standardBatch / $batch;

        return $isPackaging ? round($value, 8) : round($value * 1000, 5);
    }

    public static function isPackaging(?RawMaterial $rawMaterial): bool
    {
        return $rawMaterial !== null && trim((string) $rawMaterial->material_id) !== '';
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(FinishedGoodFormula::class, 'finished_good_formula_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
