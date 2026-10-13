<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy FORMULA.DBF menyimpan StandardA/StandardB sebagai N(15,10).
        Schema::table('finished_good_formula_items', function (Blueprint $table) {
            $table->decimal('standard_batch', 22, 10)->default(0)->change();
            $table->decimal('standard', 22, 10)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('finished_good_formula_items', function (Blueprint $table) {
            $table->decimal('standard_batch', 18, 6)->default(0)->change();
            $table->decimal('standard', 18, 6)->default(0)->change();
        });
    }
};
