<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = ['code', 'description', 'created_by', 'updated_by'];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function finishedGoods(): HasMany
    {
        return $this->hasMany(FinishedGood::class);
    }

    public function noodles(): HasMany
    {
        return $this->hasMany(Noodle::class);
    }

    public function areaNoodles(): HasMany
    {
        return $this->hasMany(AreaNoodle::class);
    }
}
