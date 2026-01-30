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
        Schema::create('messenger_logs', function (Blueprint $table) {
            $table->id();
            // К какому аккаунту привязано сообщение
            $table->foreignId('messenger_account_id')->constrained()->cascadeOnDelete();

            $table->string('title', 100)->nullable();           // Тема (например, "Наряд на работу")
            $table->text('message');                            // Текст сообщения

            $table->string('status', 20)->default('pending')->index();   // 'pending', 'sent', 'failed'
            $table->text('error_message')->nullable();          // Ошибка от API мессенджера, если есть

            $table->timestamp('sent_at')->nullable();           // Когда реально ушло
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_logs');
    }
};
