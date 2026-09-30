<?php

namespace Database\Seeders;

use App\Models\AreaNoodle;
use App\Models\FinishedGood;
use App\Models\Noodle;
use App\Models\RawMaterial;
use App\Models\User;
use App\Support\AreaNoodleCatalog;
use App\Support\FinishedGoodCatalog;
use App\Support\NoodleCatalog;
use App\Support\RawMaterialCatalog;
use Illuminate\Database\Seeder;

class MaintenanceMasterSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::where('username', 'superadmin')->value('id');

        $this->seedMaster(Noodle::class, NoodleCatalog::all()->all(), $userId);
        $this->seedMaster(AreaNoodle::class, AreaNoodleCatalog::all()->all(), $userId);
        $this->seedMaster(RawMaterial::class, RawMaterialCatalog::all()->all(), $userId);
        $this->seedMaster(FinishedGood::class, FinishedGoodCatalog::all()->all(), $userId);
    }

    private function seedMaster(string $model, array $items, ?int $userId): void
    {
        foreach ($items as $item) {
            $record = $model::firstOrNew(['code' => $item['code']]);

            if (! $record->exists) {
                $record->fill([...$item, 'created_by' => $userId, 'updated_by' => $userId])->save();
            }
        }
    }
}
