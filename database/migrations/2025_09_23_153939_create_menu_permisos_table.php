<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_permisos', function (Blueprint $table) {
            $table->id();
            $table->string('menu_item'); // 'dashboard', 'referencias', 'reportes', etc.
            $table->enum('rol', ['administrador', 'medico', 'ips']);
            $table->boolean('visible')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
            
            $table->unique(['menu_item', 'rol']);
            $table->index(['rol', 'visible', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permisos');
    }
};