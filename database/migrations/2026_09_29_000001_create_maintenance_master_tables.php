<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noodles', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 30)->unique();
            $table->string('description', 150);
            $table->string('unit', 30);
            $this->auditColumns($table);
        });

        Schema::create('area_noodles', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 30)->unique();
            $table->string('description', 150);
            $this->auditColumns($table);
        });

        Schema::create('raw_materials', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 30)->unique();
            $table->string('material_id', 50)->unique();
            $table->string('description', 150);
            $table->string('unit', 30);
            $table->decimal('wastage_all', 10, 4)->default(0);
            $table->string('currency_type', 3);
            $table->string('type_rm', 50);
            $this->auditColumns($table);
            $table->index(['currency_type', 'type_rm']);
        });

        Schema::create('finished_goods', function (Blueprint $table) {
            $this->masterColumns($table);
            $table->string('code', 30)->unique();
            $table->string('description', 150);
            $table->string('description_1', 150)->nullable();
            $table->unsignedInteger('product_type_1')->default(0);
            $table->unsignedInteger('product_type_2')->default(0);
            $table->unsignedInteger('batch')->default(0);
            $table->decimal('selling_price', 18, 2)->default(0);
            $table->char('multi_level', 1)->default('N');
            $table->char('active', 1)->default('Y');

            foreach (['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'] as $period) {
                $table->decimal("unit_cost_{$period}", 18, 2)->default(0);
                $table->decimal("unit_price_{$period}", 18, 2)->default(0);
            }

            $this->auditColumns($table);
            $table->index(['active', 'multi_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('area_noodles');
        Schema::dropIfExists('noodles');
    }

    private function masterColumns(Blueprint $table): void
    {
        $table->unsignedInteger('id')->primary();
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    }
};
