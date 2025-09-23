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
            if (!$this->indexExists('solicitudes_referencia', 'idx_estado_prioridad')) {
                $table->index(['estado', 'prioridad'], 'idx_estado_prioridad');
            }
            if (!$this->indexExists('solicitudes_referencia', 'idx_fecha_estado')) {
                $table->index(['created_at', 'estado'], 'idx_fecha_estado');
            }
            if (!$this->indexExists('solicitudes_referencia', 'idx_prioridad_fecha')) {
                $table->index(['prioridad', 'created_at'], 'idx_prioridad_fecha');
            }
            if (!$this->indexExists('solicitudes_referencia', 'idx_puntuacion_ia')) {
                $table->index('puntuacion_ia', 'idx_puntuacion_ia');
            }
        });

        // Índices para registros_medicos
        Schema::table('registros_medicos', function (Blueprint $table) {
            if (!$this->indexExists('registros_medicos', 'idx_numero_id')) {
                $table->index('numero_identificacion', 'idx_numero_id');
            }
            if (!$this->indexExists('registros_medicos', 'idx_especialidad_fecha')) {
                $table->index(['especialidad_solicitada', 'created_at'], 'idx_especialidad_fecha');
            }
            if (!$this->indexExists('registros_medicos', 'idx_fecha_creacion')) {
                $table->index('created_at', 'idx_fecha_creacion');
            }
        });

        // Índices para notificaciones
        Schema::table('notificaciones', function (Blueprint $table) {
            if (!$this->indexExists('notificaciones', 'idx_user_leida')) {
                $table->index(['user_id', 'leida'], 'idx_user_leida');
            }
            if (!$this->indexExists('notificaciones', 'idx_fecha_prioridad')) {
                $table->index(['created_at', 'prioridad'], 'idx_fecha_prioridad');
            }
        });

        // Índices para decisiones_referencia
        Schema::table('decisiones_referencia', function (Blueprint $table) {
            if (!$this->indexExists('decisiones_referencia', 'idx_solicitud_fecha')) {
                $table->index(['solicitud_referencia_id', 'created_at'], 'idx_solicitud_fecha');
            }
            if (!$this->indexExists('decisiones_referencia', 'idx_decision')) {
                $table->index('decision', 'idx_decision');
            }
        });
    }

    private function indexExists($table, $indexName)
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return count($indexes) > 0;
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