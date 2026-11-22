<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutero_dia_ciudades', function (Blueprint $table): void {
            $table->string('codigo_departamento', 20)->nullable();
            $table->string('codigo_ciudad', 20)->nullable();

            $table->index(
                ['dia_id', 'codigo_departamento', 'codigo_ciudad'],
                'rutero_dia_ciudad_codigos_index'
            );
        });

        Schema::table('rutero_paradas', function (Blueprint $table): void {
            $table->renameColumn('nombre_cliente', 'razon_social');
        });

        Schema::table('rutero_paradas', function (Blueprint $table): void {
            $table->text('direccion')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (
            DB::table('rutero_paradas')
                ->select('direccion')
                ->whereNotNull('direccion')
                ->cursor() as $parada
        ) {
            if (mb_strlen($parada->direccion) > 255) {
                throw new \RuntimeException(
                    'Hay direcciones de más de 255 caracteres. No se puede revertir sin recortarlas.'
                );
            }
        }

        Schema::table('rutero_paradas', function (Blueprint $table): void {
            $table->string('direccion', 255)->nullable()->change();
        });

        Schema::table('rutero_paradas', function (Blueprint $table): void {
            $table->renameColumn('razon_social', 'nombre_cliente');
        });

        Schema::table('rutero_dia_ciudades', function (Blueprint $table): void {
            $table->dropIndex('rutero_dia_ciudad_codigos_index');
            $table->dropColumn(['codigo_departamento', 'codigo_ciudad']);
        });
    }
};