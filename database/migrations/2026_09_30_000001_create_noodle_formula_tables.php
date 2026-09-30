<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noodle_formulas', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('noodle_id')->unique();
            $table->foreign('noodle_id')->references('id')->on('noodles')->restrictOnDelete();
            $this->auditColumns($table);
        });

        Schema::create('noodle_formula_items', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('noodle_formula_id');
            $table->unsignedInteger('finished_good_id');
            $table->decimal('standard', 18, 6)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->foreign('noodle_formula_id')->references('id')->on('noodle_formulas')->cascadeOnDelete();
            $table->foreign('finished_good_id')->references('id')->on('finished_goods')->restrictOnDelete();
            $table->index(['noodle_formula_id', 'position']);
            $this->auditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noodle_formula_items');
        Schema::dropIfExists('noodle_formulas');
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();
    }
};
