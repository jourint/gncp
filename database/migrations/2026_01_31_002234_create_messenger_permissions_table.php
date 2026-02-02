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
        Schema::create('messenger_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();   // Команда, например 'view_salary', 'create_order'
            $table->string('label', 100);           // Название команды..
            // No timestamps needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_permissions');
    }
};
