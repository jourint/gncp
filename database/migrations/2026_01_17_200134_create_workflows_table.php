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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();   // Название рабочего процесса
            $table->string('description', 255)->nullable();    // Описание рабочего процесса
            $table->boolean('is_active')->default(true);    // Активен ли рабочий процесс
            $table->decimal('price', 6, 2)->default(0.00);  // Стоимость рабочего процесса
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
