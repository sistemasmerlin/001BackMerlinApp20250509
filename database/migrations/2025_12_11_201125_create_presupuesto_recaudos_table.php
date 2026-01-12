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
        Schema::create('presupuesto_recaudo', function (Blueprint $table) {
        $table->id();

        // Asesor
        $table->string('asesor');
        $table->string('nombre_asesor');

        // Documento
        $table->string('prefijo');
        $table->string('consecutivo');

        // Condición de pago
        $table->string('cond_pago');

        // Cliente
        $table->string('nit_cliente');
        $table->string('cliente');

        // Fechas (tal como vienen en Excel)
        $table->string('fecha_doc', 8);   // YYYYMMDD
        $table->string('fecha_corte', 8); // YYYYMMDD

        // Días
        $table->integer('dias');

        // Valores y periodo
        $table->decimal('saldo', 18, 2);
        $table->string('periodo', 6);     // YYYYMM

        // Control
        $table->string('creado_por')->nullable();
        $table->boolean('estado')->default(true);
        $table->boolean('eliminado')->default(false);

        $table->timestamps();

        // Índices
        $table->index(['asesor', 'periodo']);
        $table->index(['nit_cliente', 'periodo']);

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_recaudo');
    }
};
