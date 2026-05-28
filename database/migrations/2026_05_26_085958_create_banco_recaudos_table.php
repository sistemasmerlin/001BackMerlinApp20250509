<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco_recaudos', function (Blueprint $table) {
            $table->id();

            $table->string('id_banco')->nullable();
            $table->string('descripcion_banco');

            $table->string('id_cuenta')->nullable();
            $table->string('descripcion_cuenta')->nullable();
            $table->string('numero_cuenta')->nullable();

            $table->string('id_medio_pago')->nullable();

            $table->integer('tipo_cuenta')->default(8);

            $table->boolean('estado')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco_recaudos');
    }
};