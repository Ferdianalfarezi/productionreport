<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_productions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('report_id')->nullable()->index();
            $table->date('prod_date')->nullable();
            $table->string('prod_start', 10)->nullable();
            $table->string('prod_finish', 10)->nullable();
            $table->string('part_no', 50)->nullable()->index();
            $table->string('category', 30)->nullable();
            $table->integer('qty_category')->nullable();
            $table->string('process_no', 30)->nullable();
            $table->string('machine_no', 50)->nullable()->index();
            $table->string('act_machine', 50)->nullable();
            $table->integer('shift')->nullable();
            $table->string('group_no', 10)->nullable();
            $table->tinyInteger('dandori')->nullable();
            $table->integer('stroke')->nullable();
            $table->integer('qty_ok')->nullable();
            $table->integer('qty_ng')->nullable();
            $table->string('code_ng', 20)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->decimal('wt_gross', 8, 3)->nullable();
            $table->decimal('wt_net', 8, 3)->nullable();
            $table->integer('lt_process')->nullable();
            $table->integer('lt_total')->nullable();
            $table->integer('gsph_theory')->nullable();
            $table->integer('gsph')->nullable();
            $table->integer('sph')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->string('update_by', 50)->nullable();
            $table->datetime('update_time')->nullable();
            $table->timestamps();

            $table->index(['part_no', 'machine_no', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_productions');
    }
};