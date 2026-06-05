<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibo_caja_cxcs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recibo_encabezado_id')
                ->constrained('recibos_encabezados')
                ->cascadeOnDelete();

            $table->string('F350_ID_TIPO_DOCTO')->nullable();
            $table->unsignedBigInteger('F350_CONSEC_DOCTO')->nullable();

            $table->string('F353_ID_AUXILIAR_DOCTO_CRUCE')->nullable();
            $table->string('F353_ID_SUCURSAL_DOCTO_CRUCE')->nullable();
            $table->string('F353_ID_TIPO_DOCTO_CRUCE')->nullable();
            $table->string('F353_CONSEC_DOCTO_CRUCE')->nullable();

            $table->decimal('F354_VALOR_CR', 15, 2)->default(0);
            $table->decimal('F354_VALOR_APLICADO_PP', 15, 2)->default(0);
            $table->decimal('F354_VALOR_APROVECHA', 15, 2)->default(0);
            $table->decimal('F354_VALOR_RETENCION', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibo_caja_cxcs');
    }
};
