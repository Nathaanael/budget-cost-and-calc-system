<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_material_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('raw_material_id');
            $table->string('period', 10);
            $table->decimal('usd_amount', 18, 2)->default(0);
            $table->decimal('rupiah_amount', 18, 2)->default(0);
            $table->string('source_kind', 20)->default('manual');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
            $table->unique(['raw_material_id', 'period']);
            $table->index(['period', 'source_kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_prices');
    }
};
