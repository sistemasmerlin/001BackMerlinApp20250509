<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibo_caja_ingresos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recibo_encabezado_id')
                ->constrained('recibos_encabezados')
                ->cascadeOnDelete();

            $table->string('F350_ID_CO')->nullable();
            $table->string('TIPO_DOCTO')->nullable();
            $table->unsignedBigInteger('F350_CONSEC_DOCTO')->nullable();
            $table->date('F350_FECHA')->nullable();

            $table->string('F357_ID_CAJA')->nullable();
            $table->date('F357_FECHA_RECAUDO')->nullable();

            $table->string('F350_ID_TERCERO')->nullable();
            $table->decimal('F357_VALOR_INGRESO', 15, 2)->default(0);
            $table->string('F357_ID_COBRADORCOD')->nullable();

            $table->string('F357_ID_UN')->nullable();
            $table->string('F357_ID_FE')->nullable();

            $table->text('F350_NOTAS')->nullable();

            $table->string('F351_ID_AUXILIAR_AJUSTE')->nullable();
            $table->string('F351_ID_AUXILIAR_PP')->nullable();
            $table->string('F351_ID_CCOSTO_PP')->nullable();

            $table->string('F351_ID_AUXILIAR_OTRO_ING')->nullable();
            $table->string('F351_ID_TERCERO_OTRO_ING')->nullable();
            $table->string('F351_ID_SUCURSAL_OTRO_ING')->nullable();
            $table->string('F351_ID_CO_OTRO_ING')->nullable();
            $table->string('F351_ID_UN_OTRO_ING')->nullable();

            $table->string('F357_REFERENCIA')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibo_caja_ingresos');
    }
};
