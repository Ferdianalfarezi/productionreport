<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_planning_stocks', function (Blueprint $table) {
            // Tanggal upload (date saja, bukan datetime)
            $table->date('import_date')->nullable()->after('calc_time')->index();
        });

        Schema::table('report_productions', function (Blueprint $table) {
            $table->date('import_date')->nullable()->after('update_time')->index();
        });
    }

    public function down(): void
    {
        Schema::table('e_planning_stocks', function (Blueprint $table) {
            $table->dropColumn('import_date');
        });

        Schema::table('report_productions', function (Blueprint $table) {
            $table->dropColumn('import_date');
        });
    }
};