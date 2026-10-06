<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_price_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->index();
            // Snapshots deliberately survive deletion or renaming of master records.
            $table->string('rm_code', 30);
            $table->string('fg_code', 30);
            $table->string('factory', 20);
            $table->string('period', 10);
            $table->decimal('price_before', 18, 2)->nullable();
            $table->decimal('price_after', 18, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->timestamp('matched_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_price_histories');
    }
};
