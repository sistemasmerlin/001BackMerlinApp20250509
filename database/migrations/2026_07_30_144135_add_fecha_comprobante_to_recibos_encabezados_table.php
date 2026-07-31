<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos_encabezados', function (Blueprint $table) {
            $table->date('fecha_comprobante')
                ->nullable()
                ->after('numero_soporte');
        });
    }

    public function down(): void
    {
        Schema::table('recibos_encabezados', function (Blueprint $table) {
            $table->dropColumn('fecha_comprobante');
        });
    }
};