<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos_encabezados', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_docto', 20)->default('RCM');
            $table->dateTime('fecha_recibo')->nullable();

            $table->string('id_vendedor')->nullable();
            $table->string('nombre_asesor')->nullable();
            $table->string('email_asesor')->nullable();

            $table->string('codigor_ecibo')->nullable();

            $table->string('razon_social')->nullable();
            $table->string('nit_cliente')->nullable();
            $table->string('email_cliente')->nullable();

            $table->string('numero_soporte')->nullable();

            $table->decimal('total_recibido', 15, 2)->default(0);
            $table->decimal('total_restante', 15, 2)->default(0);

            $table->string('id_banco')->nullable();

            $table->text('notas')->nullable();
            $table->text('notas_rechazo')->nullable();
            $table->text('notas_pendiente')->nullable();

            $table->decimal('retencion', 15, 2)->default(0);
            $table->decimal('reteIva', 15, 2)->default(0);

            $table->string('estado', 30)->default('RECIBIDO');

            $table->unsignedBigInteger('id_recibo_efectivo')->nullable();
            $table->decimal('valor_recibo_efectivo', 15, 2)->default(0);

            $table->string('usuarioAsignado')->nullable();

            $table->dateTime('fecha_revision')->nullable();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->dateTime('fecha_exportacion')->nullable();
            $table->dateTime('fecha_cliente_creado')->nullable();

            $table->string('adjunto_nombre_archivo')->nullable();
            $table->string('ubicacion')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos_encabezados');
    }
};
