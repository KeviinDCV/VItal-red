<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check and add indexes only if they don't exist
        $this->addIndexIfNotExists('solicitudes_referencia', 'idx_estado_prioridad_new', ['estado', 'prioridad']);
        $this->addIndexIfNotExists('solicitudes_referencia', 'idx_fecha_estado_new', ['created_at', 'estado']);
        $this->addIndexIfNotExists('solicitudes_referencia', 'idx_prioridad_fecha_new', ['prioridad', 'created_at']);
        $this->addIndexIfNotExists('solicitudes_referencia', 'idx_puntuacion_ia_new', ['puntuacion_ia']);
        
        $this->addIndexIfNotExists('registros_medicos', 'idx_numero_id_new', ['numero_identificacion']);
        $this->addIndexIfNotExists('registros_medicos', 'idx_especialidad_fecha_new', ['especialidad_solicitada', 'created_at']);
        
        $this->addIndexIfNotExists('notificaciones', 'idx_user_leida_new', ['user_id', 'leida']);
        $this->addIndexIfNotExists('notificaciones', 'idx_fecha_prioridad_new', ['created_at', 'prioridad']);
        
        $this->addIndexIfNotExists('decisiones_referencia', 'idx_solicitud_fecha_new', ['solicitud_referencia_id', 'created_at']);
        $this->addIndexIfNotExists('decisiones_referencia', 'idx_decision_new', ['decision']);
    }

    public function down()
    {
        Schema::table('solicitudes_referencia', function (Blueprint $table) {
            $table->dropIndex('idx_estado_prioridad_new');
            $table->dropIndex('idx_fecha_estado_new');
            $table->dropIndex('idx_prioridad_fecha_new');
            $table->dropIndex('idx_puntuacion_ia_new');
        });

        Schema::table('registros_medicos', function (Blueprint $table) {
            $table->dropIndex('idx_numero_id_new');
            $table->dropIndex('idx_especialidad_fecha_new');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_user_leida_new');
            $table->dropIndex('idx_fecha_prioridad_new');
        });

        Schema::table('decisiones_referencia', function (Blueprint $table) {
            $table->dropIndex('idx_solicitud_fecha_new');
            $table->dropIndex('idx_decision_new');
        });
    }

    private function addIndexIfNotExists($table, $indexName, $columns)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        
        if (empty($indexes)) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName, $columns) {
                $tableBlueprint->index($columns, $indexName);
            });
        }
    }
};