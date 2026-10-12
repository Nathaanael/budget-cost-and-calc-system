<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPlant;
use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Noodle extends Model
{
    use BelongsToPlant, HasRandomFiveDigitId;

    protected $fillable = ['plant_id', 'code', 'description', 'unit', 'created_by', 'updated_by'];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function formula(): HasOne
    {
        return $this->hasOne(NoodleFormula::class);
    }

    public function volumes(): HasMany
    {
        return $this->hasMany(VolumeNoodle::class);
    }
}
