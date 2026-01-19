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
        Schema::create('shoe_tech_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->nullable(); // Составная название + цвет
            $table->foreignId('shoe_model_id')->constrained()->cascadeOnDelete();    // Модель
            $table->foreignId('color_id')->constrained();    // Цвет
            $table->unsignedSmallInteger('material_texture_id')->nullable()->index();               // Текстура

            $table->foreignId('shoe_sole_id')->constrained();    // Подошва
            $table->foreignId('shoe_insole_id')->constrained();    // Стелька

            $table->boolean('is_active')->default(true);    // Активен ли техническая карта
            $table->string('image_path')->nullable();        // Путь к изображению технической карты

            $table->timestamps();

            $table->unique(['shoe_model_id', 'color_id', 'material_texture_id'], 'shoe_model_color_texture_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_tech_cards');
    }
};
