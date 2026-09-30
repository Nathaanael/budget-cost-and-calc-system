<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoodleFormula extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = ['noodle_id', 'created_by', 'updated_by'];

    public function noodle(): BelongsTo
    {
        return $this->belongsTo(Noodle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(NoodleFormulaItem::class)->orderBy('position');
    }
}
