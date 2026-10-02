<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_material_prices', function (Blueprint $table) {
            $table->unsignedInteger('reference_id')->nullable()->after('source_kind');
            $table->decimal('exchange_rate', 15, 2)->nullable()->after('reference_id');
            $table->timestamp('calculated_at')->nullable()->after('exchange_rate');
            $table->foreign('reference_id')->references('id')->on('references')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('raw_material_prices', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
            $table->dropColumn(['reference_id', 'exchange_rate', 'calculated_at']);
        });
    }
};
