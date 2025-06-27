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
        Schema::create('intereses_cartera', function (Blueprint $table) {
            $table->id();
            $table->string('prefijo', 10)->default('');
            $table->string('consecutivo', 20)->default('');
            $table->decimal('valor_base', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('valor_factura', 15, 2)->default(0);
            $table->decimal('abono', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->date('fecha_factura');
            $table->date('fecha_hoy');
            $table->integer('dias_transcurridos')->default(0);
            $table->string('asesor', 100)->default('');
            $table->string('condicion_pago', 50)->default('');
            $table->decimal('valor_diario_interes', 15, 2)->default(0);
            $table->decimal('valor_acumulado_interes', 15, 2)->default(0);
            $table->string('razon_social', 150)->default('');
            $table->string('nit', 50)->default('');
            $table->string('estado', 20)->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intereses_carteras');
    }
};
