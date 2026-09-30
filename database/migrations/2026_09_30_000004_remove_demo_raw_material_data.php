<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('raw_materials')->whereIn('code', [
            'RM-0001', 'RM-0002', 'RM-0003', 'RM-0004', 'RM-0005',
            'RM-0006', 'RM-0007', 'RM-0008', 'RM-0009', 'RM-0010',
        ])->delete();
    }

    public function down(): void
    {
    }
};
