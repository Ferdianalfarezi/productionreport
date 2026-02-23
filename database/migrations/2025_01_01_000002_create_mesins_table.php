<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesins', function (Blueprint $table) {
            $table->id();
            $table->string('line_machine');
            $table->string('machine_no')->unique();
            $table->string('tonage')->nullable();
            $table->string('line')->nullable();
            $table->integer('gsph_theory')->default(0);
            $table->text('remarks')->nullable();
            $table->string('sw_line')->nullable();
            $table->string('sw_no')->nullable();
            $table->string('update_by')->default('-');
            $table->timestamp('update_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesins');
    }
};