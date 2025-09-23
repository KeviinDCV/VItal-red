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
        Schema::create('automatic_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_referencia_id')->constrained('solicitudes_referencia')->onDelete('cascade');
            $table->foreignId('response_template_id')->nullable()->constrained('response_templates')->onDelete('set null');
            $table->string('recipient_email');
            $table->string('recipient_name');
            $table->string('subject');
            $table->text('content');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->enum('delivery_status', ['pending', 'delivered', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->decimal('response_time_seconds', 8, 2)->nullable();
            $table->json('variables_used')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'sent_at']);
            $table->index(['solicitud_referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automatic_responses');
    }
};
