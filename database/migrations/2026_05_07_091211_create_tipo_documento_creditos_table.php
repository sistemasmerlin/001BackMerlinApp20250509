<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_documentos_credito', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->text('descripcion')->nullable();

            // cantidad mínima requerida
            $table->integer('cantidad_minima')->default(1);

            // cantidad máxima permitida
            $table->integer('cantidad_maxima')->default(1);

            // obligatorio SI/NO
            $table->boolean('obligatorio')->default(true);

            // permite múltiples archivos
            $table->boolean('multiple')->default(false);

            // activo
            $table->boolean('estado')->default(true);

            // orden visual
            $table->integer('orden')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_documentos_credito');
    }
};