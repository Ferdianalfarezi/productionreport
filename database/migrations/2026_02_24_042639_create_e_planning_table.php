<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_planning_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('e_planning_id')->nullable(); // kolom ID dari Excel
            $table->string('judgement')->nullable();
            $table->string('part_no_fg')->nullable();
            $table->string('part_no_parent')->nullable();
            $table->string('part_no_child')->nullable()->index();
            $table->string('store')->nullable();
            $table->string('process')->nullable();
            $table->string('line')->nullable()->index();
            $table->string('rack_no')->nullable();
            $table->integer('seq_calc')->nullable();
            $table->integer('qty_kbn')->nullable();
            $table->integer('qty_consume')->nullable();
            $table->integer('qty_lot')->nullable();
            $table->integer('stock_min')->nullable();
            $table->integer('stock_max')->nullable();
            $table->integer('stock_prod')->nullable();
            $table->integer('stock_store')->nullable();
            $table->string('status')->nullable();
            $table->string('calc_by')->nullable();
            $table->timestamp('calc_time')->nullable();
            $table->timestamps();

            // Index gabungan untuk lookup cepat
            $table->index(['part_no_child', 'line']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_planning_stocks');
    }
};