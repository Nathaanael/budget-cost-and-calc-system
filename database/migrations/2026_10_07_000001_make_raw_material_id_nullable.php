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
            $table->string('material_id', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('raw_materials')
            ->whereNull('material_id')
            ->orderBy('id')
            ->eachById(function (object $rawMaterial): void {
                DB::table('raw_materials')
                    ->where('id', $rawMaterial->id)
                    ->update(['material_id' => "AUTO-{$rawMaterial->id}"]);
            });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->string('material_id', 50)->nullable(false)->change();
        });
    }
};
