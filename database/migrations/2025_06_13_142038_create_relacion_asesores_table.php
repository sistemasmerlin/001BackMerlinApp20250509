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
        Schema::create('relacion_asesores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asesor_id');
            $table->unsignedBigInteger('relacionado_id');
            $table->timestamps();

            $table->foreign('asesor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('relacionado_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['asesor_id', 'relacionado_id']); // Evita duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relacion_asesores');
    }
};
