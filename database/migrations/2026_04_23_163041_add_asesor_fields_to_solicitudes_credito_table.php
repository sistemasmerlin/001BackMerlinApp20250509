<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->string('codigo_asesor')->nullable()->after('user_id');
            $table->string('nombre_asesor')->nullable()->after('codigo_asesor');
            $table->string('cedula_asesor')->nullable()->after('nombre_asesor');
            $table->string('email_asesor')->nullable()->after('cedula_asesor');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_asesor',
                'nombre_asesor',
                'cedula_asesor',
                'email_asesor',
            ]);
        });
    }
};
