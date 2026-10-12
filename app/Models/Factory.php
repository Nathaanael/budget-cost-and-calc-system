<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factory extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = ['code', 'description', 'created_by', 'updated_by'];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function areaSlots(): HasMany
    {
        return $this->hasMany(FactoryArea::class)->orderBy('position');
    }
}
