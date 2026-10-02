<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volume_noodles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('area_noodle_id');
            $table->unsignedInteger('noodle_id');

            foreach (['le_july', 'le_august', 'le_september', 'le_october', 'le_november', 'le_december'] as $field) {
                $table->decimal($field, 18, 2)->default(0);
            }

            $table->decimal('total_le', 18, 2)->default(0);

            foreach (['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'] as $field) {
                $table->decimal($field, 18, 2)->default(0);
            }

            $table->decimal('total_aop', 18, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('area_noodle_id')->references('id')->on('area_noodles')->restrictOnDelete();
            $table->foreign('noodle_id')->references('id')->on('noodles')->restrictOnDelete();
            $table->unique(['area_noodle_id', 'noodle_id']);
            $table->index('noodle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volume_noodles');
    }
};
