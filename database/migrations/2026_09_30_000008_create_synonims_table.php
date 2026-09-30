<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('synonims', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('raw_material_id')->unique();
            $table->unsignedInteger('finished_good_id');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->restrictOnDelete();
            $table->foreign('finished_good_id')->references('id')->on('finished_goods')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('finished_good_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('synonims');
    }
};
