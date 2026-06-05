<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibo_cajas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recibo_encabezado_id')
                ->constrained('recibos_encabezados')
                ->cascadeOnDelete();

            $table->string('F350_ID_TIPO_DOCTO')->nullable();
            $table->unsignedBigInteger('F350_CONSEC_DOCTO')->nullable();
            $table->string('F358_ID_MEDIOS_PAGO')->nullable();
            $table->decimal('F358_VALOR', 15, 2)->default(0);
            $table->string('F358_REFERENCIA_OTROS')->nullable();
            $table->date('F358_FECHA_CONSIGNACION')->nullable();
            $table->string('f358_docto_banco_cg')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibo_cajas');
    }
};
