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
            $table->string('name', 150)->nullable()->index(); // Составная название + цвет
            $table->foreignId('shoe_model_id')->constrained()->cascadeOnDelete();    // Модель
            $table->foreignId('color_id')->constrained()->restrictOnDelete();    // Цвет

            $table->foreignId('shoe_sole_id')->constrained()->restrictOnDelete();    // Подошва

            $table->boolean('is_active')->default(true);    // Активен ли техническая карта
            $table->string('image_path', 255)->nullable();        // Путь к изображению технической карты

            $table->timestamps();

            $table->unique(['shoe_model_id', 'color_id'], 'shoe_model_color_unique');
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
