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
        Schema::create('motivo_reporte_visita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_visita_id')->constrained()->onDelete('cascade');
            $table->foreignId('motivos_visita_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivo_reporte_visita');
    }
};
