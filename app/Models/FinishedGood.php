<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinishedGood extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = [
        'code', 'description', 'description_1', 'product_type_1', 'product_type_2',
        'batch', 'selling_price', 'multi_level', 'active',
        'unit_cost_current', 'unit_price_current', 'unit_cost_le', 'unit_price_le',
        'unit_cost_qtr_1', 'unit_price_qtr_1', 'unit_cost_qtr_2', 'unit_price_qtr_2',
        'unit_cost_qtr_3', 'unit_price_qtr_3', 'unit_cost_qtr_4', 'unit_price_qtr_4',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'product_type_1' => 'integer',
            'product_type_2' => 'integer',
            'batch' => 'integer',
            'selling_price' => 'decimal:2',
            'unit_cost_current' => 'decimal:2',
            'unit_price_current' => 'decimal:2',
            'unit_cost_le' => 'decimal:2',
            'unit_price_le' => 'decimal:2',
            'unit_cost_qtr_1' => 'decimal:2',
            'unit_price_qtr_1' => 'decimal:2',
            'unit_cost_qtr_2' => 'decimal:2',
            'unit_price_qtr_2' => 'decimal:2',
            'unit_cost_qtr_3' => 'decimal:2',
            'unit_price_qtr_3' => 'decimal:2',
            'unit_cost_qtr_4' => 'decimal:2',
            'unit_price_qtr_4' => 'decimal:2',
        ];
    }

    public function noodleFormulaItems(): HasMany
    {
        return $this->hasMany(NoodleFormulaItem::class);
    }

    public function rawMaterialFormula(): HasOne
    {
        return $this->hasOne(FinishedGoodFormula::class);
    }

    public function synonims(): HasMany
    {
        return $this->hasMany(Synonim::class);
    }
}
