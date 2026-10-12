<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_PLANT_ID = 28720;

    public function up(): void
    {
        if (! Schema::hasColumn('noodles', 'plant_id')) {
            Schema::table('noodles', function (Blueprint $table) {
                $table->dropUnique('noodles_code_unique');
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique(['plant_id', 'code'], 'noodles_plant_code_unique');
            });
        }

        if (! Schema::hasColumn('area_noodles', 'plant_id')) {
            Schema::table('area_noodles', function (Blueprint $table) {
                $table->dropUnique('area_noodles_code_unique');
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique(['plant_id', 'code'], 'area_noodles_plant_code_unique');
            });
        }

        if (! Schema::hasColumn('noodle_formulas', 'plant_id')) {
            Schema::table('noodle_formulas', function (Blueprint $table) {
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->index(['plant_id', 'noodle_id'], 'noodle_formulas_plant_noodle_index');
            });
        }

        if (! Schema::hasColumn('volume_noodles', 'plant_id')) {
            Schema::table('volume_noodles', function (Blueprint $table) {
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->index(['plant_id', 'area_noodle_id', 'noodle_id'], 'volume_noodles_plant_area_noodle_index');
            });
        }

        if (! Schema::hasColumn('factory_areas', 'plant_id')) {
            Schema::table('factory_areas', function (Blueprint $table) {
                $table->index('factory_id', 'factory_areas_factory_id_index');
            });
            Schema::table('factory_areas', function (Blueprint $table) {
                $table->dropUnique('factory_areas_factory_id_position_unique');
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique(['plant_id', 'factory_id', 'position'], 'factory_areas_plant_factory_position_unique');
            });
        }

        if (! Schema::hasColumn('volume_calculation_state', 'plant_id')) {
            Schema::table('volume_calculation_state', function (Blueprint $table) {
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique('plant_id');
            });
        }

        DB::table('volume_calculation_state')->insertOrIgnore([
            'id' => 2,
            'plant_id' => 28730,
            'calculated_at' => null,
            'calculated_by' => null,
        ]);
    }

    public function down(): void
    {
        DB::table('volume_calculation_state')->where('plant_id', 28730)->delete();

        Schema::table('volume_calculation_state', function (Blueprint $table) {
            $table->dropUnique(['plant_id']);
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
        });

        Schema::table('factory_areas', function (Blueprint $table) {
            $table->dropUnique('factory_areas_plant_factory_position_unique');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique(['factory_id', 'position']);
            $table->dropIndex('factory_areas_factory_id_index');
        });

        Schema::table('volume_noodles', function (Blueprint $table) {
            $table->dropIndex('volume_noodles_plant_area_noodle_index');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
        });

        Schema::table('noodle_formulas', function (Blueprint $table) {
            $table->dropIndex('noodle_formulas_plant_noodle_index');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
        });

        Schema::table('area_noodles', function (Blueprint $table) {
            $table->dropUnique('area_noodles_plant_code_unique');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique('code');
        });

        Schema::table('noodles', function (Blueprint $table) {
            $table->dropUnique('noodles_plant_code_unique');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique('code');
        });
    }
};
