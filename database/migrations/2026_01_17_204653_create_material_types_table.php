<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('material_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // Название типа материала
            $table->unsignedSmallInteger('unit_id')->nullable()->index();  // Единица измерения
            $table->string('description')->nullable();    // Описание типа материала
            $table->boolean('is_active')->default(true);    // Активен ли тип материала
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_types');
    }
};
