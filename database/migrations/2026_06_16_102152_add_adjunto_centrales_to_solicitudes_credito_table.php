<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->string('centrales_riesgo_disk')->nullable()->after('comentario_reporte_centrales');
            $table->string('centrales_riesgo_path')->nullable()->after('centrales_riesgo_disk');
            $table->string('centrales_riesgo_nombre')->nullable()->after('centrales_riesgo_path');
        });
    }

    public function down()
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn([
                'centrales_riesgo_disk',
                'centrales_riesgo_path',
                'centrales_riesgo_nombre',
            ]);
        });
    }
};
