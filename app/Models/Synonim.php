<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Synonim extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = [
        'raw_material_id',
        'finished_good_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'raw_material_id' => 'integer',
        'finished_good_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function finishedGood(): BelongsTo
    {
        return $this->belongsTo(FinishedGood::class)->withoutGlobalScope('plant');
    }
}
