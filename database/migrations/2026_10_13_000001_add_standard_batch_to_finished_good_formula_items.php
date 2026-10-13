<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy FORMULA.DBF: StandardA = standard per batch, StandardB = standard per unit (kolom `standard`).
        Schema::table('finished_good_formula_items', function (Blueprint $table) {
            $table->decimal('standard_batch', 18, 6)->default(0)->after('raw_material_id');
        });
    }

    public function down(): void
    {
        Schema::table('finished_good_formula_items', function (Blueprint $table) {
            $table->dropColumn('standard_batch');
        });
    }
};
