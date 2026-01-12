<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Normalizar datos inválidos ANTES de cambiar el ENUM
        DB::table('presupuestos_comerciales')
            ->whereNull('categoria')
            ->orWhereNotIn('categoria', ['llantas', 'repuestos', 'pirelli', 'total'])
            ->update(['categoria' => 'llantas']); // o 'repuestos', según prefieras

        // 2) Cambiar ENUM para agregar 'pirelli' y 'total'
        DB::statement("
            ALTER TABLE presupuestos_comerciales
            MODIFY categoria ENUM('llantas', 'repuestos', 'pirelli', 'total') NOT NULL
        ");
    }

    public function down(): void
    {
        // Volvemos al enum original (sin pirelli ni total)
        DB::statement("
            ALTER TABLE presupuestos_comerciales
            MODIFY categoria ENUM('llantas', 'repuestos') NOT NULL
        ");
    }
};
