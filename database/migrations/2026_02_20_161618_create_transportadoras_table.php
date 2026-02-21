<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportadoras', function (Blueprint $table) {
            $table->id();

            $table->string('nit', 30)->index();
            $table->string('razon_social', 200);
            $table->string('direccion', 200)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('ciudad', 100)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nit']); // si tu negocio lo permite (recomendado)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportadoras');
    }
};
