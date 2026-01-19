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
        Schema::create('order_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();    // Заказ
            $table->foreignId('shoe_tech_card_id')->constrained()->restrictOnDelete();    // Техническая карта
            $table->unsignedSmallInteger('size_id')->nullable()->index();    // Размер пары
            $table->unsignedMediumInteger('quantity')->default(1); // количество пар
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_positions');
    }
};
