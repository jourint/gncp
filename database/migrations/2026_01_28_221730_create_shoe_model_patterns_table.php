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
        Schema::create('shoe_model_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoe_model_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('size_id')->nullable(); // Null, если лекало общее
            $table->string('file_path', 255);
            $table->string('file_name', 100); // Оригинальное имя (например, "Боковина_внешняя.pdf")
            $table->string('type', 20)->default('vector'); // vector (для печати), raster (фото), scan
            $table->decimal('scale', 5, 2)->default(100.00); // Масштаб для печати, на всякий случай
            $table->string('note', 255)->nullable(); // Комментарий (например, "учесть припуск 3мм")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_model_patterns');
    }
};
