<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listas_precios_subcategorias', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150);

            // unidades o subtotal
            $table->string('tipo_calculo', 20);

            $table->boolean('activa')->default(true);

            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();

            $table->unique('nombre');
        });

        Schema::create('listas_precios_subcategoria_marcas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subcategoria_id')
                ->constrained('listas_precios_subcategorias')
                ->cascadeOnDelete();

            // Debe corresponder al valor recibido en marca_excel.
            $table->string('marca_excel', 150);

            $table->timestamps();

            // Por ahora, una marca pertenece a una sola subcategoría.
            $table->unique('marca_excel', 'lp_subcat_marca_unica');

            $table->index('subcategoria_id', 'lp_subcat_marca_subcat_idx');
        });

        Schema::create('listas_precios_subcategoria_rangos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subcategoria_id')
                ->constrained('listas_precios_subcategorias')
                ->cascadeOnDelete();

            // Se aplicará cuando el valor calculado sea >= desde.
            $table->decimal('desde', 18, 2);

            // Si es null, el rango no tiene límite superior.
            $table->decimal('hasta', 18, 2)->nullable();

            $table->decimal('porcentaje', 5, 2);

            $table->timestamps();

            $table->index(
                ['subcategoria_id', 'desde'],
                'lp_subcat_rango_desde_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listas_precios_subcategoria_rangos');
        Schema::dropIfExists('listas_precios_subcategoria_marcas');
        Schema::dropIfExists('listas_precios_subcategorias');
    }
};