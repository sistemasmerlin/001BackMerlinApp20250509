<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutero_dia_ciudades', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dia_id')
                ->constrained('rutero_dias')
                ->cascadeOnDelete();

            $table->string('ciudad', 100);
            $table->string('departamento', 100);
            $table->timestamps();

            $table->unique(
                ['dia_id', 'departamento', 'ciudad'],
                'rutero_dia_ciudad_unique'
            );
        });

        Schema::create('rutero_paradas', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dia_ciudad_id')
                ->constrained('rutero_dia_ciudades')
                ->cascadeOnDelete();

            $table->string('nit', 32);
            $table->string('id_sucursal', 32);
            $table->string('punto_envio_id', 32);
            $table->string('nombre_cliente');
            $table->string('direccion')->nullable();
            $table->timestamps();

            $table->unique(
                ['dia_ciudad_id', 'nit', 'id_sucursal', 'punto_envio_id'],
                'rutero_parada_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutero_paradas');
        Schema::dropIfExists('rutero_dia_ciudades');
    }
};