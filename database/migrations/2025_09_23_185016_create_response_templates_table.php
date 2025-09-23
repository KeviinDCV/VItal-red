<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('response_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialty')->default('general');
            $table->enum('priority', ['ROJO', 'VERDE'])->default('VERDE');
            $table->string('subject');
            $table->text('content');
            $table->json('variables')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['specialty', 'priority', 'active']);
            $table->index(['active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_templates');
    }
};
