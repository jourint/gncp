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
        Schema::create('messenger_bot_states', function (Blueprint $table) {
            $table->id();
            // Привязываем к аккаунту мессенджера
            $table->foreignId('messenger_account_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('command_name', 100)->nullable();
            $table->string('step', 100)->nullable();
            $table->jsonb('context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_bot_states');
    }
};
