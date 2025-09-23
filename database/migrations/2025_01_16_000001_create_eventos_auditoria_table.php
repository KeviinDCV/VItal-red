<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('eventos_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('accion');
            $table->string('recurso');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->enum('nivel_riesgo', ['BAJO', 'MEDIO', 'ALTO', 'CRITICO'])->default('BAJO');
            $table->json('detalles')->nullable();
            $table->json('ubicacion')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['nivel_riesgo', 'created_at']);
            $table->index(['accion', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventos_auditoria');
    }
};