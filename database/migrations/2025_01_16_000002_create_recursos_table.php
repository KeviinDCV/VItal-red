<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['MEDICO', 'ENFERMERO', 'CAMA', 'EQUIPO', 'SALA']);
            $table->string('nombre');
            $table->string('especialidad')->nullable();
            $table->string('ubicacion');
            $table->enum('estado', ['DISPONIBLE', 'OCUPADO', 'MANTENIMIENTO', 'FUERA_SERVICIO'])->default('DISPONIBLE');
            $table->integer('capacidad_maxima')->nullable();
            $table->integer('capacidad_actual')->default(0);
            $table->enum('turno', ['MAÑANA', 'TARDE', 'NOCHE', '24H'])->nullable();
            $table->json('contacto')->nullable();
            $table->json('metricas')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index(['ubicacion', 'estado']);
            $table->index(['estado', 'updated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('recursos');
    }
};