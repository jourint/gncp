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
        Schema::create('material_movements', function (Blueprint $table) {
            $table->id();
            $table->morphs('movable');  // Polymorphic relation to various material types
            $table->enum('type', ['income', 'outcome', 'write-off'])->index();  // Тип движения
            $table->string('description', 255)->nullable();    // Описание движения
            $table->decimal('quantity', 7, 2)->default(0.00);    // Количество материала
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();  // Пользователь
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_movements');
    }
};
