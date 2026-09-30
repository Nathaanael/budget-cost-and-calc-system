<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryArea extends Model
{
    use HasRandomFiveDigitId;

    protected $fillable = ['factory_id', 'area_noodle_id', 'position'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function areaNoodle(): BelongsTo
    {
        return $this->belongsTo(AreaNoodle::class);
    }
}
