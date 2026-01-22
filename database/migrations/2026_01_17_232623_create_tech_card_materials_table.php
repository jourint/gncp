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
        Schema::create('tech_card_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoe_tech_card_id')->constrained()->cascadeOnDelete();    // Техническая карта
            $table->foreignId('material_id')->constrained()->restrictOnDelete();    // Материал
            $table->decimal('quantity', 6, 2)->default(0.00); // Количество материала на 1 пару
            $table->timestamps();

            $table->unique(['shoe_tech_card_id', 'material_id'], 'shoe_tech_card_material_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tech_card_materials');
    }
};
