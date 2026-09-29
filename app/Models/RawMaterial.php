<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = [
        'code', 'material_id', 'description', 'unit', 'wastage_all',
        'currency_type', 'type_rm', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['wastage_all' => 'decimal:4'];
    }
}
