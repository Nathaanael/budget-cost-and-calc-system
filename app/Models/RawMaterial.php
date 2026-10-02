<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawMaterial extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = [
        'code', 'material_id', 'description', 'unit', 'wastage_all',
        'currency_type', 'type_rm', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['wastage_all' => 'decimal:4'];
    }

    public function finishedGoodFormulaItems(): HasMany
    {
        return $this->hasMany(FinishedGoodFormulaItem::class);
    }

    public function synonim(): HasOne
    {
        return $this->hasOne(Synonim::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RawMaterialPrice::class);
    }
}
