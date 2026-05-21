<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud_credito_referencias', function (Blueprint $table) {
            $table->string('quien_da_referencia')->nullable()->after('cupo_credito');
            $table->decimal('cupo_asignado', 15, 2)->nullable()->after('quien_da_referencia');
            $table->string('antiguedad_comercial')->nullable()->after('cupo_asignado');
            $table->string('promedio_pago')->nullable()->after('antiguedad_comercial');
            $table->string('cheques_devueltos')->nullable()->after('promedio_pago');
            $table->string('activo')->nullable()->after('cheques_devueltos');
            $table->text('concepto')->nullable()->after('activo');
            $table->date('fecha_referencia')->nullable()->after('concepto');
            $table->date('ultimo_despacho')->nullable()->after('fecha_referencia');

            $table->foreignId('verifico_referencia')
                ->nullable()
                ->after('ultimo_despacho')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_credito_referencias', function (Blueprint $table) {
            $table->dropForeign(['verifico_referencia']);

            $table->dropColumn([
                'quien_da_referencia',
                'cupo_asignado',
                'antiguedad_comercial',
                'promedio_pago',
                'cheques_devueltos',
                'activo',
                'concepto',
                'fecha_referencia',
                'ultimo_despacho',
                'verifico_referencia',
            ]);
        });
    }
};