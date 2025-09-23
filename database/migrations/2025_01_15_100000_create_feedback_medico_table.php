<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedback_medico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_referencia')->onDelete('cascade');
            $table->foreignId('medico_id')->nullable()->constrained('users')->onDelete('set null');
            $table->float('puntuacion_original');
            $table->float('puntuacion_correcta');
            $table->text('feedback');
            $table->float('diferencia');
            $table->timestamps();
            
            $table->index(['created_at', 'diferencia']);
            $table->index('solicitud_id');
        });

        Schema::create('ai_learning_patterns', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->json('patterns');
            $table->integer('total_feedback');
            $table->timestamps();
            
            $table->unique('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_learning_patterns');
        Schema::dropIfExists('feedback_medico');
    }
};