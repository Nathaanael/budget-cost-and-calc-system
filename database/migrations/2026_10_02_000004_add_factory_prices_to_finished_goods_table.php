<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PERIODS = ['current', 'le', 'qtr_1', 'qtr_2', 'qtr_3', 'qtr_4'];

    public function up(): void
    {
        Schema::table('finished_goods', function (Blueprint $table) {
            foreach (['cikampek', 'semarang', 'surabaya', 'palembang'] as $factory) {
                $table->decimal("pe_{$factory}", 18, 2)->default(0);
            }

            foreach (['semarang', 'surabaya', 'palembang'] as $factory) {
                foreach (self::PERIODS as $period) {
                    $table->decimal("unit_price_{$factory}_{$period}", 18, 2)->default(0);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('finished_goods', function (Blueprint $table) {
            $columns = [];

            foreach (['cikampek', 'semarang', 'surabaya', 'palembang'] as $factory) {
                $columns[] = "pe_{$factory}";
            }

            foreach (['semarang', 'surabaya', 'palembang'] as $factory) {
                foreach (self::PERIODS as $period) {
                    $columns[] = "unit_price_{$factory}_{$period}";
                }
            }

            $table->dropColumn($columns);
        });
    }
};
