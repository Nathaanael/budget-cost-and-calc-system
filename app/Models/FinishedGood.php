<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use App\Models\Concerns\BelongsToPlant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinishedGood extends Model
{
    use BelongsToPlant, HasRandomFiveDigitId;

    protected $fillable = [
        'plant_id', 'code', 'description', 'description_1', 'product_type_1', 'product_type_2',
        'batch', 'selling_price', 'multi_level', 'active',
        'unit_cost_current', 'unit_price_current', 'unit_cost_le', 'unit_price_le',
        'unit_cost_qtr_1', 'unit_price_qtr_1', 'unit_cost_qtr_2', 'unit_price_qtr_2',
        'unit_cost_qtr_3', 'unit_price_qtr_3', 'unit_cost_qtr_4', 'unit_price_qtr_4',
        'pe_cikampek', 'pe_semarang', 'pe_surabaya', 'pe_palembang',
        'unit_price_semarang_current', 'unit_price_semarang_le',
        'unit_price_semarang_qtr_1', 'unit_price_semarang_qtr_2', 'unit_price_semarang_qtr_3', 'unit_price_semarang_qtr_4',
        'unit_price_surabaya_current', 'unit_price_surabaya_le',
        'unit_price_surabaya_qtr_1', 'unit_price_surabaya_qtr_2', 'unit_price_surabaya_qtr_3', 'unit_price_surabaya_qtr_4',
        'unit_price_palembang_current', 'unit_price_palembang_le',
        'unit_price_palembang_qtr_1', 'unit_price_palembang_qtr_2', 'unit_price_palembang_qtr_3', 'unit_price_palembang_qtr_4',
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
            'pe_cikampek' => 'decimal:2',
            'pe_semarang' => 'decimal:2',
            'pe_surabaya' => 'decimal:2',
            'pe_palembang' => 'decimal:2',
            'unit_price_semarang_current' => 'decimal:2',
            'unit_price_semarang_le' => 'decimal:2',
            'unit_price_semarang_qtr_1' => 'decimal:2',
            'unit_price_semarang_qtr_2' => 'decimal:2',
            'unit_price_semarang_qtr_3' => 'decimal:2',
            'unit_price_semarang_qtr_4' => 'decimal:2',
            'unit_price_surabaya_current' => 'decimal:2',
            'unit_price_surabaya_le' => 'decimal:2',
            'unit_price_surabaya_qtr_1' => 'decimal:2',
            'unit_price_surabaya_qtr_2' => 'decimal:2',
            'unit_price_surabaya_qtr_3' => 'decimal:2',
            'unit_price_surabaya_qtr_4' => 'decimal:2',
            'unit_price_palembang_current' => 'decimal:2',
            'unit_price_palembang_le' => 'decimal:2',
            'unit_price_palembang_qtr_1' => 'decimal:2',
            'unit_price_palembang_qtr_2' => 'decimal:2',
            'unit_price_palembang_qtr_3' => 'decimal:2',
            'unit_price_palembang_qtr_4' => 'decimal:2',
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
