<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('references', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('code', 2)->unique();
            $table->string('description_1', 30);
            $table->string('description_2', 30);
            $table->string('period', 8);
            $table->string('period_description', 15);

            foreach (['current', 'le', '1', '2', '3', '4'] as $period) {
                $table->decimal("rate_{$period}", 15, 2)->default(0);
            }

            foreach (['ckp', 'smg', 'sby'] as $plant) {
                foreach (['current', 'le', '1', '2', '3', '4'] as $period) {
                    $table->decimal("pe_{$plant}_{$period}", 10, 2)->default(0);
                }
            }

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
