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
        Schema::create('shoe_sole_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoe_sole_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('size_id')->index(); // ID вашего Sushi-класса Size
            $table->integer('stock_quantity')->default(0); // количество на складе
            $table->timestamps();
            $table->unique(['shoe_sole_id', 'size_id'], 'shoe_sole_size_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_sole_items');
    }
};
