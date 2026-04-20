<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comisiones_asesores', function (Blueprint $table) {
            $table->id();

            $table->string('periodo', 6); // Ej: 202603
            $table->string('cod_asesor', 20);

            // Relación con el asesor en users
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Tipo de dato guardado: llantas_ppto, llantas_ventas, llantas_comision, etc.
            $table->string('tipo', 100);

            $table->decimal('valor', 18, 4)->default(0);

            // Usuario que realizó la última actualización
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('periodo');
            $table->index('cod_asesor');
            $table->index('tipo');
            $table->index('user_id');
            $table->index('updated_by');

            $table->unique(
                ['periodo', 'cod_asesor', 'tipo'],
                'comisiones_asesores_periodo_codasesor_tipo_unique'
            );
        });    
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones_asesores');
    }
};
