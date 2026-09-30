<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $demoNoodleCodes = [
            '2000001', '2000002', '2000003', '2000004', '2000005',
            '2000006', '2000007', '2000008', '2000009', '2000010',
        ];
        $demoFinishedGoodCodes = [
            'FG-0001', 'FG-0002', 'FG-0003', 'FG-0004', 'FG-0005',
            'FG-0006', 'FG-0007', 'FG-0008', 'FG-0009', 'FG-0010',
        ];

        $noodleIds = DB::table('noodles')->whereIn('code', $demoNoodleCodes)->pluck('id');
        $finishedGoodIds = DB::table('finished_goods')->whereIn('code', $demoFinishedGoodCodes)->pluck('id');
        $formulaIds = DB::table('noodle_formulas')->whereIn('noodle_id', $noodleIds)->pluck('id');

        DB::table('noodle_formula_items')
            ->whereIn('noodle_formula_id', $formulaIds)
            ->orWhereIn('finished_good_id', $finishedGoodIds)
            ->delete();
        DB::table('noodle_formulas')->whereIn('id', $formulaIds)->delete();
        DB::table('finished_goods')->whereIn('id', $finishedGoodIds)->delete();
        DB::table('noodles')->whereIn('id', $noodleIds)->delete();
    }

    public function down(): void
    {
    }
};
