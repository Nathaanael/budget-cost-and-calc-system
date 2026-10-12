<?php

namespace App\Support;

use App\Models\Plant;

class PlantContext
{
    private ?Plant $plant = null;

    public function set(Plant $plant): void
    {
        $this->plant = $plant;
    }

    public function plant(): Plant
    {
        return $this->plant ??= Plant::query()->orderBy('code')->firstOrFail();
    }

    public function id(): int
    {
        return (int) $this->plant()->getKey();
    }
}
