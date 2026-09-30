<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinishedGoodFormula extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = ['finished_good_id', 'created_by', 'updated_by'];

    public function finishedGood(): BelongsTo
    {
        return $this->belongsTo(FinishedGood::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FinishedGoodFormulaItem::class)->orderBy('position');
    }
}
