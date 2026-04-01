<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_credito_direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_credito_id')->constrained('solicitudes_credito')->cascadeOnDelete();

            $table->string('contacto')->nullable();
            $table->string('direccion')->nullable();
            $table->string('cod_depto', 10)->nullable();
            $table->string('depto', 120)->nullable();
            $table->string('cod_ciudad', 10)->nullable();
            $table->string('ciudad', 120)->nullable();
            $table->string('telefono', 30)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_credito_direcciones');
    }
};
