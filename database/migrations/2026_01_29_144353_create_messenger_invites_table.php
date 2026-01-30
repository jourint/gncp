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
        Schema::create('messenger_invites', function (Blueprint $table) {
            $table->id();
            $table->morphs('invitable'); // Создаст invitable_id и invitable_type
            $table->string('token', 64)->unique();
            $table->string('driver', 20)->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_invites');
    }
};
