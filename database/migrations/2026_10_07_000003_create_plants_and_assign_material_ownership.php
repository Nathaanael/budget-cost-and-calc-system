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
        if (! Schema::hasTable('plants')) {
            Schema::create('plants', function (Blueprint $table) {
                $table->unsignedInteger('id')->primary();
                $table->string('code', 10)->unique();
                $table->string('description', 100);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        foreach ([
            ['id' => self::DEFAULT_PLANT_ID, 'code' => '2872', 'description' => 'Ingredient'],
            ['id' => 28730, 'code' => '2873', 'description' => 'Blending & Packing'],
        ] as $plant) {
            $existing = DB::table('plants')->where('code', $plant['code'])->first();

            if ($existing) {
                DB::table('plants')->where('code', $plant['code'])->update([
                    'description' => $plant['description'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('plants')->insert([...$plant, 'updated_at' => now(), 'created_at' => now()]);
            }
        }

        if (! Schema::hasColumn('raw_materials', 'plant_id')) {
            Schema::table('raw_materials', function (Blueprint $table) {
                $table->dropUnique('raw_materials_code_unique');
                $table->dropUnique('raw_materials_material_id_unique');
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique(['plant_id', 'code'], 'raw_materials_plant_code_unique');
                $table->unique(['plant_id', 'material_id'], 'raw_materials_plant_material_id_unique');
            });
        }

        if (! Schema::hasColumn('finished_goods', 'plant_id')) {
            Schema::table('finished_goods', function (Blueprint $table) {
                $table->dropUnique('finished_goods_code_unique');
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique(['plant_id', 'code'], 'finished_goods_plant_code_unique');
            });
        }

        if (! Schema::hasColumn('matching_price_histories', 'source_plant_id')) {
            Schema::table('matching_price_histories', function (Blueprint $table) {
                $table->unsignedInteger('source_plant_id')->nullable();
                $table->unsignedInteger('target_plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('source_plant_id')->references('id')->on('plants')->nullOnDelete();
                $table->foreign('target_plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->index(['target_plant_id', 'matched_at'], 'matching_history_target_plant_index');
            });
        }

        DB::table('matching_price_histories')->whereNull('target_plant_id')->update([
            'target_plant_id' => self::DEFAULT_PLANT_ID,
        ]);

        if (! Schema::hasColumn('finished_good_volumes', 'plant_id')) {
            Schema::table('finished_good_volumes', function (Blueprint $table) {
                $table->dropUnique('finished_good_volumes_area_noodle_id_finished_good_id_unique');
                $table->unsignedInteger('plant_id')->default(self::DEFAULT_PLANT_ID);
                $table->foreign('plant_id')->references('id')->on('plants')->restrictOnDelete();
                $table->unique(['plant_id', 'area_noodle_id', 'finished_good_id'], 'fg_volumes_plant_area_good_unique');
                $table->index(['plant_id', 'area_code', 'fg_code'], 'fg_volumes_plant_codes_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('finished_good_volumes', function (Blueprint $table) {
            $table->dropIndex('fg_volumes_plant_codes_index');
            $table->dropUnique('fg_volumes_plant_area_good_unique');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique(['area_noodle_id', 'finished_good_id']);
        });

        Schema::table('matching_price_histories', function (Blueprint $table) {
            $table->dropIndex('matching_history_target_plant_index');
            $table->dropForeign(['source_plant_id']);
            $table->dropForeign(['target_plant_id']);
            $table->dropColumn(['source_plant_id', 'target_plant_id']);
        });

        Schema::table('finished_goods', function (Blueprint $table) {
            $table->dropUnique('finished_goods_plant_code_unique');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique('code');
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropUnique('raw_materials_plant_code_unique');
            $table->dropUnique('raw_materials_plant_material_id_unique');
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique('code');
            $table->unique('material_id');
        });

        Schema::dropIfExists('plants');
    }
};
