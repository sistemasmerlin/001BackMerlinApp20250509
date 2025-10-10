<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('presupuestos_comerciales', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_asesor', 20)->index();
            $table->string('periodo', 6)->index(); // formato YYYYMM (e.g. 202509)
            $table->decimal('presupuesto', 18, 2);

            $table->string('marca', 100)->nullable()->index();
            $table->enum('categoria', ['llantas', 'repuestos'])->index();

            $table->string('clasificacion_asesor', 50)->nullable()->index();
            $table->string('tipo_presupuesto', 50)->index(); // p.ej. 'ventas', 'unidades', etc.

            $table->timestamps();

            // Evita duplicados del mismo asesor/periodo/marca/categoria/tipo
            $table->unique([
                'codigo_asesor','periodo','marca','categoria','tipo_presupuesto'
            ], 'uniq_presu_comercial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos_comerciales');
    }
};
