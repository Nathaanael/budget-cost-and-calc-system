<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_good_formulas', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('finished_good_id')->unique();
            $table->foreign('finished_good_id')->references('id')->on('finished_goods')->restrictOnDelete();
            $this->auditColumns($table);
        });

        Schema::create('finished_good_formula_items', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('finished_good_formula_id');
            $table->unsignedInteger('raw_material_id');
            $table->decimal('standard', 18, 6)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->foreign('finished_good_formula_id')->references('id')->on('finished_good_formulas')->cascadeOnDelete();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->restrictOnDelete();
            $table->index(['finished_good_formula_id', 'position'], 'fg_formula_items_position_index');
            $this->auditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_good_formula_items');
        Schema::dropIfExists('finished_good_formulas');
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();
    }
};
