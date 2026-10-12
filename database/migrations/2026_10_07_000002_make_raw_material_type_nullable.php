<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->string('type_rm', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('raw_materials')->whereNull('type_rm')->update(['type_rm' => 'UNSPECIFIED']);

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->string('type_rm', 50)->nullable(false)->change();
        });
    }
};
