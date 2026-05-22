<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->string('carta_cierre_disk')->nullable()->after('comentario_cierre_aprobado');
            $table->string('carta_cierre_path')->nullable()->after('carta_cierre_disk');
            $table->string('carta_cierre_nombre')->nullable()->after('carta_cierre_path');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn([
                'carta_cierre_disk',
                'carta_cierre_path',
                'carta_cierre_nombre',
            ]);
        });
    }
};