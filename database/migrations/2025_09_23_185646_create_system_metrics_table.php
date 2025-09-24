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
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->decimal('metric_value', 15, 4);
            $table->enum('metric_type', ['gauge', 'counter', 'histogram']);
            $table->string('category')->default('system');
            $table->timestamp('timestamp');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['metric_name', 'timestamp']);
            $table->index(['category', 'timestamp']);
            $table->index(['timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
    }
};
