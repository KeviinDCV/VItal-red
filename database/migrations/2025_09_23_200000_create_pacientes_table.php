<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('documento')->unique();
            $table->enum('tipo_documento', ['CC', 'TI', 'CE', 'PP', 'RC'])->default('CC');
            $table->date('fecha_nacimiento');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('eps')->nullable();
            $table->enum('regimen', ['contributivo', 'subsidiado', 'especial'])->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('contacto_emergencia')->nullable();
            $table->string('telefono_emergencia')->nullable();
            $table->timestamps();

            $table->index(['documento']);
            $table->index(['nombre', 'apellidos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};