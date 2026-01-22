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
        Schema::create('shoe_soles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->index(); // Код подошвы
            $table->foreignId('color_id')->constrained('colors')->restrictOnDelete();   // Цвет подошвы
            $table->boolean('is_active')->default(true);    // Активен ли подошва
            $table->timestamps();
            $table->unique(['name', 'color_id'], 'unique_shoe_sole');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_soles');
    }
};
