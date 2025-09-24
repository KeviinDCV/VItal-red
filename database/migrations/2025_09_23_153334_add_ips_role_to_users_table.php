<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modificar el enum para incluir 'ips'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('administrador', 'medico', 'ips') NOT NULL DEFAULT 'medico'");
        
        // Agregar campo opcional para asociar usuario IPS con institución
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('ips_id')->nullable()->constrained('ips')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ips_id']);
            $table->dropColumn('ips_id');
        });
        
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('administrador', 'medico') NOT NULL DEFAULT 'medico'");
    }
};