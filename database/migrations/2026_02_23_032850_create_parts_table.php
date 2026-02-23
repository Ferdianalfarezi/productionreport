<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_no_child')->nullable();
            $table->string('line')->nullable();
            $table->decimal('qty_kbn', 15, 2)->nullable();
            $table->string('category')->nullable();       // dari data_cavity col I
            $table->integer('qty_category')->nullable();  // dari data_cavity col J
            $table->string('update_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
