<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom KETERANGAN pada template Excel Formula FG.
        Schema::table('finished_good_formulas', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('finished_good_id');
        });
    }

    public function down(): void
    {
        Schema::table('finished_good_formulas', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
