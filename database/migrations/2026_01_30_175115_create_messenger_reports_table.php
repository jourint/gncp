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
        Schema::create('messenger_reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable'); // Связь с Customer или Employee
            $table->date('production_date')->index();
            $table->string('report_type')->default('production')->index();
            $table->text('content');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['reportable_id', 'reportable_type', 'production_date', 'report_type'], 'report_identity_full_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_reports');
    }
};
