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
        if (!Schema::hasTable('ai_classification_logs')) {
            Schema::create('ai_classification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_referencia_id')->constrained('solicitudes_referencia')->onDelete('cascade');
            $table->enum('classification_result', ['ROJO', 'VERDE']);
            $table->decimal('confidence_score', 5, 4);
            $table->integer('processing_time_ms');
            $table->string('algorithm_version')->default('1.0');
            $table->json('input_data');
            $table->json('decision_factors');
            $table->boolean('manual_override')->default(false);
            $table->integer('feedback_score')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['classification_result', 'confidence_score'], 'idx_classification_confidence');
            $table->index(['created_at'], 'idx_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_classification_logs');
    }
};
