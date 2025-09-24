<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_referencia', function (Blueprint $table) {
            $table->id();
            
            // Relación con registro médico original
            $table->foreignId('registro_medico_id')->constrained('registros_medicos')->onDelete('cascade');
            
            // Información básica de la solicitud
            $table->string('codigo_solicitud')->unique(); // Código único generado automáticamente
            $table->enum('prioridad', ['ROJO', 'VERDE'])->default('VERDE');
            $table->enum('estado', ['PENDIENTE', 'ACEPTADO', 'NO_ADMITIDO'])->default('PENDIENTE');
            
            // Fechas importantes
            $table->timestamp('fecha_solicitud');
            $table->timestamp('fecha_limite')->nullable(); // 48 horas después de solicitud
            $table->timestamp('fecha_decision')->nullable();
            
            // Información del algoritmo IA
            $table->decimal('puntuacion_ia', 5, 2)->nullable(); // Puntuación del algoritmo (0-100)
            $table->json('factores_priorizacion')->nullable(); // Factores que influyeron en la decisión
            
            // Observaciones
            $table->text('observaciones_ia')->nullable(); // Observaciones generadas por IA
            $table->text('observaciones_medico')->nullable(); // Observaciones del médico que decide
            
            // Usuario que procesó la solicitud
            $table->foreignId('procesado_por')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['estado', 'prioridad']);
            $table->index(['fecha_solicitud']);
            $table->index(['fecha_limite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_referencia');
    }
};