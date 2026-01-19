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
        Schema::create('order_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();    // Заказ
            $table->foreignId('order_position_id')->constrained()->cascadeOnDelete();    // Позиция заказа
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();    // Сотрудник
            $table->unsignedSmallInteger('quantity')->default(1); // количество пар
            $table->decimal('price_per_pair', 8, 2)->default(0.00); // Цена за пару из тип + коэф модели при формировании
            $table->boolean('is_paid')->default(false); // Оплачено ли сотруднику за эту работу
            $table->timestamps();
            $table->unique(['order_id', 'order_position_id', 'employee_id'], 'order_employee_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_employees');
    }
};
