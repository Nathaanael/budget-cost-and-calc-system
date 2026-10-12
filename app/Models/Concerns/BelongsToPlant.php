<?php

namespace App\Models\Concerns;

use App\Models\Plant;
use App\Support\PlantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToPlant
{
    protected static function bootBelongsToPlant(): void
    {
        static::addGlobalScope('plant', function (Builder $builder): void {
            $builder->where($builder->qualifyColumn('plant_id'), app(PlantContext::class)->id());
        });

        static::creating(function ($model): void {
            $model->plant_id ??= app(PlantContext::class)->id();
        });
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
}
