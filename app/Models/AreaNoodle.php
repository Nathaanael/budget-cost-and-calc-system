<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPlant;
use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AreaNoodle extends Model
{
    use BelongsToPlant, HasRandomFiveDigitId;

    protected $fillable = ['plant_id', 'code', 'description', 'created_by', 'updated_by'];

    public function factorySlots(): HasMany
    {
        return $this->hasMany(FactoryArea::class);
    }

    public function volumes(): HasMany
    {
        return $this->hasMany(VolumeNoodle::class);
    }
}
