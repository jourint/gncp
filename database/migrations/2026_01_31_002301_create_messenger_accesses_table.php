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
        Schema::create('messenger_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('messenger_permission_id')->constrained()->cascadeOnDelete();
            $table->morphs('accessible'); // accessible_id, accessible_type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_accesses');
    }
};
