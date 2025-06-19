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
        Schema::create('promocion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');
            $table->string('tipo');
            $table->string('descripcion')->nullable();
            $table->boolean('acumulado')->default(false);
            $table->string('modelo')->nullable();
            $table->decimal('desde', 10, 2)->nullable();
            $table->decimal('hasta', 10, 2)->nullable();
            $table->decimal('descuento', 5, 2);
            $table->boolean('estado')->default(true);
            $table->boolean('eliminado')->default(false);
            $table->string('creado_por'); // string
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_detalles');
    }
};
