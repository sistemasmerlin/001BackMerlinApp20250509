<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_bancarios', function (Blueprint $table) {
            $table->foreignId('recibo_encabezado_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('recibos_encabezados')
                ->nullOnDelete();

            $table->string('estado', 30)
                ->default('DISPONIBLE')
                ->after('procesado');

            $table->timestamp('fecha_aplicacion')
                ->nullable()
                ->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_bancarios', function (Blueprint $table) {
            $table->dropForeign([
                'recibo_encabezado_id',
            ]);

            $table->dropUnique([
                'recibo_encabezado_id',
            ]);

            $table->dropColumn([
                'recibo_encabezado_id',
                'estado',
                'fecha_aplicacion',
            ]);
        });
    }
};