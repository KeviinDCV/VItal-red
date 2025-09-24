<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_ia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->json('parametros');
            $table->boolean('activo')->default(true);
            $table->boolean('activa')->default(true);
            $table->string('version')->default('1.0');
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('creado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_ia');
    }
};