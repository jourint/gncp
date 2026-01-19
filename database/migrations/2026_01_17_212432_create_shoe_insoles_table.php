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
        Schema::create('shoe_insoles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->index(); // Код резака для стельки
            $table->boolean('is_black')->default(true); // Черная расцветка, 0 - нет, 1 - да
            $table->boolean('is_active')->default(true);    // Активна ли стелька
            $table->jsonb('tech_card')->nullable(); // Технические карты в формате JSON
            $table->timestamps();
            $table->unique(['name', 'is_black'], 'shoe_insole_name_black_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_insoles');
    }
};
