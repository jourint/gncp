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
        Schema::create('shoe_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();  // Название типа обуви
            $table->boolean('is_active')->default(true);   // Активен ли тип обуви
            $table->decimal('price_cutting', 5, 2)->default(0.00); // Цена за раскрой
            $table->decimal('price_sewing', 5, 2)->default(0.00); // Цена за пошив
            $table->decimal('price_shoemaker', 5, 2)->default(0.00);    // Цена за сапожник
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_types');
    }
};
