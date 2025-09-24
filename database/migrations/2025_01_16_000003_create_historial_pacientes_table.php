<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('historial_pacientes')) {
            Schema::create('historial_pacientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->json('consultas')->nullable();
            $table->json('referencias')->nullable();
            $table->timestamp('ultima_consulta')->nullable();
            $table->integer('total_consultas')->default(0);
            $table->integer('total_referencias')->default(0);
            $table->timestamps();

            $table->index(['paciente_id', 'ultima_consulta']);
            $table->index(['ultima_consulta']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('historial_pacientes');
    }
};