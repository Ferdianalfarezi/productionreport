<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('e_planning_stocks', function (Blueprint $table) {
        $table->decimal('calc_prod', 10, 2)->nullable()->after('stock_store');
    });
}

public function down()
{
    Schema::table('e_planning_stocks', function (Blueprint $table) {
        $table->dropColumn('calc_prod');
    });
}
};
