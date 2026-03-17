<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orms', function (Blueprint $table) {
            $table->dateTime('fecha_llegada_bodega')->nullable()->after('fecha_recibido_transportadora');
        });
    }

    public function down(): void
    {
        Schema::table('orms', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_llegada_bodega',
            ]);
        });
    }
};
