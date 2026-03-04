<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();
            $table->string('break_name');          // e.g. "Istirahat Pagi"
            $table->tinyInteger('shift')->nullable(); // 1, 2, atau null = semua shift
            $table->time('break_start');             // HH:MM:SS
            $table->unsignedSmallInteger('duration'); // dalam menit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
};