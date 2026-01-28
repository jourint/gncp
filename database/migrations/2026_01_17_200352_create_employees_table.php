<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\JobPosition;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();       // Имя
            $table->unsignedTinyInteger('job_position_id')->default(JobPosition::None->value)->index();  // Цех Sushi
            $table->string('phone', 20)->unique();  // Телефон
            $table->boolean('is_active')->default(true)->index();    // Активен ли сотрудник
            $table->decimal('skill_level', 5, 2)->default(1.00);    // Уровень навыков, для деления количества пар из заказа
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
