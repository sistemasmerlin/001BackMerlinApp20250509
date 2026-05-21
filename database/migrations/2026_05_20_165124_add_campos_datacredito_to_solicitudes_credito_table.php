<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->integer('datacredito_score')->nullable()->after('comentario_reporte_centrales');
            $table->decimal('datacredito_ingresos_ventas', 15, 2)->nullable()->after('datacredito_score');
            $table->decimal('datacredito_nivel_endeudamiento', 15, 2)->nullable()->after('datacredito_ingresos_ventas');
            $table->string('datacredito_sector_reporte_negativo')->nullable()->after('datacredito_nivel_endeudamiento');
            $table->decimal('datacredito_valor_reporte_negativo', 15, 2)->nullable()->after('datacredito_sector_reporte_negativo');
            $table->string('datacredito_resultado')->nullable()->after('datacredito_valor_reporte_negativo');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn([
                'datacredito_score',
                'datacredito_ingresos_ventas',
                'datacredito_nivel_endeudamiento',
                'datacredito_sector_reporte_negativo',
                'datacredito_valor_reporte_negativo',
                'datacredito_resultado',
            ]);
        });
    }
};