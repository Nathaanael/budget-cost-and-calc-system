<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropUnique('raw_materials_plant_material_id_unique');
            $table->index(['plant_id', 'material_id'], 'raw_materials_plant_material_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropIndex('raw_materials_plant_material_id_index');
            $table->unique(['plant_id', 'material_id'], 'raw_materials_plant_material_id_unique');
        });
    }
};
