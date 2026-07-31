<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos_encabezados', function (Blueprint $table) {
            $table->renameColumn('codigor_ecibo', 'codigo_recibo');
        });
    }

    public function down(): void
    {
        Schema::table('recibos_encabezados', function (Blueprint $table) {
            $table->renameColumn('codigo_recibo', 'codigor_ecibo');
        });
    }
};