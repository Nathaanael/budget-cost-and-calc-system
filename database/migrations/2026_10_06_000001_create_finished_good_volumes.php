<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volume_calculation_state', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
        });
        DB::table('volume_calculation_state')->insert(['id' => 1]);

        Schema::create('finished_good_volumes', function (Blueprint $table) {
            $table->id();
            // Snapshot identifiers and labels preserve the last calculated result.
            $table->unsignedInteger('area_noodle_id');
            $table->unsignedInteger('finished_good_id');
            $table->string('area_code');
            $table->string('fg_code');
            $table->string('description');
            $table->integer('product_type_1');
            $table->integer('product_type_2');
            foreach (['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december',
                'le_july', 'le_august', 'le_september', 'le_october', 'le_november', 'le_december', 'total_aop', 'total_le'] as $field) {
                $table->decimal($field, 24, 8)->default(0);
            }
            $table->unique(['area_noodle_id', 'finished_good_id']);
            $table->index(['area_code', 'fg_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_good_volumes');
        Schema::dropIfExists('volume_calculation_state');
    }
};
