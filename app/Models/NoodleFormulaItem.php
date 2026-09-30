<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoodleFormulaItem extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = [
        'noodle_formula_id',
        'finished_good_id',
        'standard',
        'position',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return ['standard' => 'decimal:6', 'position' => 'integer'];
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(NoodleFormula::class, 'noodle_formula_id');
    }

    public function finishedGood(): BelongsTo
    {
        return $this->belongsTo(FinishedGood::class);
    }
}
