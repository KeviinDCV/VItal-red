<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisiones_referencia', function (Blueprint $table) {
            $table->id();
            
            // Relación con solicitud de referencia
            $table->foreignId('solicitud_referencia_id')->constrained('solicitudes_referencia')->onDelete('cascade');
            
            // Usuario que tomó la decisión
            $table->foreignId('decidido_por')->constrained('users')->onDelete('cascade');
            
            // Información de la decisión
            $table->enum('decision', ['ACEPTADO', 'NO_ADMITIDO']);
            $table->text('justificacion'); // Justificación obligatoria de la decisión
            
            // Información adicional para casos aceptados
            $table->string('especialista_asignado')->nullable();
            $table->string('servicio_asignado')->nullable();
            $table->date('fecha_cita_estimada')->nullable();
            $table->date('fecha_cita')->nullable();
            $table->text('instrucciones_paciente')->nullable();
            
            // Información adicional para casos no admitidos
            $table->string('motivo_rechazo')->nullable();
            $table->text('recomendaciones_alternativas')->nullable();
            
            // Metadatos
            $table->timestamp('fecha_decision');
            $table->json('datos_adicionales')->nullable(); // Para información extra que pueda ser útil
            
            $table->timestamps();
            
            // Índices
            $table->index(['decision']);
            $table->index(['fecha_decision']);
            $table->index(['decidido_por']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisiones_referencia');
    }
};