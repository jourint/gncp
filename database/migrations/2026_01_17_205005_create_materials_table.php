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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // наименование материала
            $table->foreignId('material_type_id')->constrained('material_types')->restrictOnDelete();   // Тип материала
            $table->foreignId('color_id')->nullable()->constrained('colors')->restrictOnDelete();               // Цвет материала
            $table->boolean('is_active')->default(true)->index();    // Активен ли материал
            $table->decimal('stock_quantity', 12, 2)->default(0.00); // материал в наличии на складе
            $table->timestamps();

            $table->unique(['name', 'material_type_id', 'color_id'], 'unique_material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
