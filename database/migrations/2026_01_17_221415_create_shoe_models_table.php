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
        Schema::create('shoe_models', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->index();    // Название модели
            $table->string('description', 255)->nullable();    // Описание модели
            $table->foreignId('shoe_type_id')->constrained('shoe_types')->restrictOnDelete();    // Тип обуви

            $table->foreignId('shoe_insole_id')->nullable()->constrained()->restrictOnDelete();    // Стелька
            $table->foreignId('puff_id')->nullable()->constrained('puffs')->restrictOnDelete();    // Подносок
            $table->foreignId('counter_id')->nullable()->constrained('counters')->restrictOnDelete(); // Задник

            $table->decimal('price_coeff_cutting', 5, 2)->default(1.00); // закройка 1.00 = 100% от суммы в shoe_types
            $table->decimal('price_coeff_sewing', 5, 2)->default(1.00); // пошив 1.00 = 100%
            $table->decimal('price_coeff_shoemaker', 5, 2)->default(1.00); // сапожник 1.00 = 100%

            $table->jsonb('available_sizes')->nullable(); // e.g. [36, 37, 38, 39, 40, 41, 42]
            $table->jsonb('workflows')->nullable(); // Дополнительные рабочие процессы для модели

            $table->boolean('is_active')->default(true)->index();    // Активен ли модель

            $table->timestamps();

            $table->unique(['name', 'shoe_type_id'], 'shoe_models_name_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_models');
    }
};
