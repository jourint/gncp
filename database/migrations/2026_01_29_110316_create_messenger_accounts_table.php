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
        Schema::create('messenger_accounts', function (Blueprint $table) {
            $table->id();
            // Создает поля messengerable_id и messengerable_type
            // Это позволит привязать аккаунт к любой модели (Employee, Customer и т.д.)
            $table->morphs('messengerable');

            $table->string('driver', 20); // 'telegram', 'viber'
            $table->string('user_id', 64)->index(); // ID пользователя в мессенджере
            $table->string('chat_id', 100)->index(); // Внутренний ID чата мессенджера

            // Доп. поля для удобства менеджера в АРМ
            $table->string('identifier', 100)->nullable(); // Например, @username или номер телефона
            $table->string('nickname', 100)->nullable();   // Как человек подписан в мессенджере

            $table->boolean('is_active')->default(true)->index(); // Активен ли аккаунт для получения уведомлений
            $table->timestamps();

            // Защита от дублей: один и тот же чат не может быть привязан дважды к одному драйверу
            $table->unique(['driver', 'user_id'], 'unique_driver_user_messenger');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_accounts');
    }
};
