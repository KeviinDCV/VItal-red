<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Índices para solicitudes_referencia (tabla más consultada)
        Schema::table('solicitudes_referencia', function (Blueprint $table) {
            $table->index(['estado', 'prioridad'], 'idx_estado_prioridad');
            $table->index(['created_at', 'estado'], 'idx_fecha_estado');
            $table->index(['prioridad', 'created_at'], 'idx_prioridad_fecha');
            $table->index('puntuacion_ia', 'idx_puntuacion_ia');
        });

        // Índices para registros_medicos
        Schema::table('registros_medicos', function (Blueprint $table) {
            $table->index('numero_identificacion', 'idx_numero_id');
            $table->index(['especialidad_solicitada', 'created_at'], 'idx_especialidad_fecha');
            $table->index('created_at', 'idx_fecha_creacion');
        });

        // Índices para notificaciones
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->index(['user_id', 'leida'], 'idx_user_leida');
            $table->index(['created_at', 'prioridad'], 'idx_fecha_prioridad');
        });

        // Índices para decisiones_referencia
        Schema::table('decisiones_referencia', function (Blueprint $table) {
            $table->index(['solicitud_id', 'created_at'], 'idx_solicitud_fecha');
            $table->index('decision', 'idx_decision');
        });
    }

    public function down()
    {
        Schema::table('solicitudes_referencia', function (Blueprint $table) {
            $table->dropIndex('idx_estado_prioridad');
            $table->dropIndex('idx_fecha_estado');
            $table->dropIndex('idx_prioridad_fecha');
            $table->dropIndex('idx_puntuacion_ia');
        });

        Schema::table('registros_medicos', function (Blueprint $table) {
            $table->dropIndex('idx_numero_id');
            $table->dropIndex('idx_especialidad_fecha');
            $table->dropIndex('idx_fecha_creacion');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_user_leida');
            $table->dropIndex('idx_fecha_prioridad');
        });

        Schema::table('decisiones_referencia', function (Blueprint $table) {
            $table->dropIndex('idx_solicitud_fecha');
            $table->dropIndex('idx_decision');
        });
    }
};