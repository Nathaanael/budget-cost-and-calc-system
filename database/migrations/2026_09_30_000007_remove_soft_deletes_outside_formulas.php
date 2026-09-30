<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'noodles',
        'area_noodles',
        'raw_materials',
        'finished_goods',
        'references',
        'factories',
        'factory_areas',
    ];

    public function up(): void
    {
        $deletedNoodleIds = $this->deletedIds('noodles');
        $deletedAreaIds = $this->deletedIds('area_noodles');
        $deletedRawMaterialIds = $this->deletedIds('raw_materials');
        $deletedFinishedGoodIds = $this->deletedIds('finished_goods');
        $deletedFactoryIds = $this->deletedIds('factories');

        if ($deletedNoodleIds !== []) {
            $formulaIds = DB::table('noodle_formulas')->whereIn('noodle_id', $deletedNoodleIds)->pluck('id');
            DB::table('noodle_formula_items')->whereIn('noodle_formula_id', $formulaIds)->delete();
            DB::table('noodle_formulas')->whereIn('id', $formulaIds)->delete();
        }

        if ($deletedFinishedGoodIds !== []) {
            DB::table('noodle_formula_items')->whereIn('finished_good_id', $deletedFinishedGoodIds)->delete();
            $formulaIds = DB::table('finished_good_formulas')->whereIn('finished_good_id', $deletedFinishedGoodIds)->pluck('id');
            DB::table('finished_good_formula_items')->whereIn('finished_good_formula_id', $formulaIds)->delete();
            DB::table('finished_good_formulas')->whereIn('id', $formulaIds)->delete();
        }

        if ($deletedRawMaterialIds !== []) {
            DB::table('finished_good_formula_items')->whereIn('raw_material_id', $deletedRawMaterialIds)->delete();
        }

        if (Schema::hasColumn('factory_areas', 'deleted_at')) {
            DB::table('factory_areas')
                ->whereNotNull('deleted_at')
                ->when($deletedFactoryIds !== [], fn ($query) => $query->orWhereIn('factory_id', $deletedFactoryIds))
                ->when($deletedAreaIds !== [], fn ($query) => $query->orWhereIn('area_noodle_id', $deletedAreaIds))
                ->delete();
        }

        foreach (['references', 'factories', 'finished_goods', 'raw_materials', 'area_noodles', 'noodles'] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                DB::table($table)->whereNotNull('deleted_at')->delete();
            }
        }

        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropSoftDeletes());
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->softDeletes());
            }
        }
    }

    private function deletedIds(string $table): array
    {
        if (! Schema::hasColumn($table, 'deleted_at')) {
            return [];
        }

        return DB::table($table)->whereNotNull('deleted_at')->pluck('id')->all();
    }
};
