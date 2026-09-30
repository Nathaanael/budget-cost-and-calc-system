<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factories', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('code', 2)->unique();
            $table->string('description', 20);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('factory_areas', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('factory_id');
            $table->unsignedInteger('area_noodle_id');
            $table->unsignedTinyInteger('position');
            $table->foreign('factory_id')->references('id')->on('factories')->cascadeOnDelete();
            $table->foreign('area_noodle_id')->references('id')->on('area_noodles')->restrictOnDelete();
            $table->unique(['factory_id', 'position']);
            $table->index('area_noodle_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_areas');
        Schema::dropIfExists('factories');
    }
};
