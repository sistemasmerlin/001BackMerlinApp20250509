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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_asesor');
            $table->string('nombre_asesor');
            $table->string('nit');
            $table->string('razon_social');
            $table->string('lista_precio');
            $table->string('correo_cliente');
            $table->string('id_sucursal');
            $table->string('condicion_pago');
            $table->dateTime('fecha_pedido');
            $table->string('estado_siesa');
            $table->string('estado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['prefijo', 'nota', 'orden_compra', 'id_estado_pedido']);
        });
    }
};
