<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orms', function (Blueprint $table) {
            $table->id();

            // Relación con PQRS
            $table->foreignId('pqrs_id')
                ->constrained('pqrs')
                ->cascadeOnDelete();

            // Datos cliente (snapshot en el momento de crear la ORM)
            $table->string('razon_social', 200)->nullable();
            $table->string('nit', 30)->index();
            $table->string('direccion', 200)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 50)->nullable();

            // Transportadora
            $table->foreignId('transportadora_id')
                ->nullable()
                ->constrained('transportadoras')
                ->nullOnDelete();

            // Datos logísticos
            $table->integer('lps')->nullable(); // asumo "lps" (si era "lios", cámbialo)
            $table->integer('cajas')->nullable();
            $table->decimal('peso', 10, 2)->nullable();

            $table->decimal('valor_declarado', 14, 2)->nullable();

            // Estado
            $table->enum('estado', ['creada', 'en_tramite', 'cerrada'])
                ->default('creada')
                ->index();

            $table->text('comentarios')->nullable();

            // Fechas
            $table->date('fecha_recogida_programada')->nullable();
            $table->dateTime('fecha_recibido_transportadora')->nullable();

            // Usuario que recibe (cuando llegue)
            $table->foreignId('usuario_recibe_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orms');
    }
};
