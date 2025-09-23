<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_pacientes', function (Blueprint $table) {
            $table->id();
            
            // Relación con solicitud de referencia
            $table->foreignId('solicitud_referencia_id')->constrained('solicitudes_referencia')->onDelete('cascade');
            
            // Estado del seguimiento
            $table->enum('estado_seguimiento', ['INGRESADO', 'NO_INGRESADO', 'CANCELADO', 'EN_PROCESO'])->default('EN_PROCESO');
            
            // Información de ingreso
            $table->timestamp('fecha_ingreso_real')->nullable();
            $table->string('servicio_ingreso')->nullable();
            $table->string('medico_tratante')->nullable();
            
            // Información de no ingreso
            $table->enum('motivo_no_ingreso', [
                'NO_SE_PRESENTO',
                'FALLECIO_TRASLADO', 
                'ACEPTO_OTRA_IPS',
                'PACIENTE_RECHAZO',
                'PROBLEMA_TRANSPORTE',
                'OTRO'
            ])->nullable();
            $table->text('observaciones_no_ingreso')->nullable();
            
            // Evolución del paciente (si ingresó)
            $table->text('evolucion_clinica')->nullable();
            $table->date('fecha_alta_estimada')->nullable();
            $table->date('fecha_alta_real')->nullable();
            $table->enum('tipo_alta', ['MEDICA', 'VOLUNTARIA', 'REMISION', 'FALLECIMIENTO'])->nullable();
            
            // Contrarreferencia
            $table->boolean('requiere_contrarreferencia')->default(false);
            $table->text('contrarreferencia_generada')->nullable();
            $table->timestamp('fecha_contrarreferencia')->nullable();
            
            // Campos adicionales para compatibilidad
            $table->foreignId('medico_seguimiento_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('estado', ['programado', 'ingresado', 'no_ingreso', 'completado'])->default('programado');
            $table->date('fecha_ingreso')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();
            
            // Usuario que actualiza el seguimiento
            $table->foreignId('actualizado_por')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Índices
            $table->index(['estado_seguimiento']);
            $table->index(['fecha_ingreso_real']);
            $table->index(['requiere_contrarreferencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_pacientes');
    }
};