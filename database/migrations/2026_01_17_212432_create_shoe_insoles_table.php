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
        Schema::create('shoe_insoles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->index(); // например: 513 Вкладная, 168 обтяжная
            $table->boolean('is_soft_texon')->default(false); // жёсткий или мягкий тексон
            $table->enum('type', ['inset', 'fitting', 'half-insole']); // вкладная, обтяжная, полустелька
            $table->boolean('has_egg')->default(false); // нужна ли "яичка", , 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['name', 'is_soft_texon', 'type'], 'unique_shoe_insole');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_insoles');
    }
};
