<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->string('reporte_centrales_riesgo')->default('sin_estado')->after('estado'); 
            $table->text('comentario_reporte_centrales')->nullable()->after('reporte_centrales_riesgo');
            $table->string('numero_cotizacion')->nullable()->after('estado'); 
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn([
                'reporte_centrales_riesgo',
                'comentario_reporte_centrales',
                'numero_cotizacion'
            ]);
        });
    }
};
