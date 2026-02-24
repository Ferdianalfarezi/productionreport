<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_configs', function (Blueprint $table) {
            $table->id();
            $table->string('line', 100)->nullable();
            $table->string('mesin', 100)->nullable();
            $table->string('update_by', 100)->nullable();
            $table->timestamps();

            $table->unique(['line', 'mesin']); // cegah duplikat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_configs');
    }
};