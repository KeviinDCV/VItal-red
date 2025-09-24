<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ips', function (Blueprint $table) {
            $table->id();
            
            // Información básica de la IPS
            $table->string('codigo_prestador')->unique(); // Código único del prestador
            $table->string('nombre');
            $table->string('nit')->unique();
            $table->enum('tipo_ips', ['HOSPITAL', 'CLINICA', 'CENTRO_SALUD', 'ESE', 'PRIVADA']);
            
            // Ubicación
            $table->string('departamento');
            $table->string('municipio');
            $table->string('direccion');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            
            // Servicios disponibles
            $table->boolean('tiene_hospitalizacion')->default(false);
            $table->boolean('tiene_urgencias')->default(false);
            $table->boolean('tiene_uci')->default(false);
            $table->boolean('tiene_cirugia')->default(false);
            $table->json('especialidades_disponibles')->nullable(); // Array de especialidades
            
            // Información de contacto para referencias
            $table->string('contacto_referencias')->nullable();
            $table->string('telefono_referencias')->nullable();
            $table->string('email_referencias')->nullable();
            
            // Estado y configuración
            $table->boolean('activa')->default(true);
            $table->boolean('acepta_referencias')->default(true);
            $table->integer('capacidad_diaria')->nullable(); // Capacidad estimada de pacientes por día
            
            // Metadatos
            $table->json('configuracion_adicional')->nullable();
            $table->timestamp('fecha_registro');
            
            $table->timestamps();
            
            // Índices
            $table->index(['departamento', 'municipio']);
            $table->index(['activa', 'acepta_referencias']);
            $table->index(['tiene_hospitalizacion', 'tiene_urgencias']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ips');
    }
};