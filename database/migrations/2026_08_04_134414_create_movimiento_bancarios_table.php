<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_bancarios', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identificación de la importación
            |--------------------------------------------------------------------------
            */

            $table->string('archivo_origen')->nullable();
            $table->unsignedInteger('fila_origen')->nullable();
            $table->dateTime('fecha_importacion')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Información del movimiento
            |--------------------------------------------------------------------------
            */

            $table->date('fecha_movimiento');

            $table->string('oficina_canal')->nullable();

            $table->string('descripcion_movimiento', 500);

            /*
            |--------------------------------------------------------------------------
            | Referencias
            |--------------------------------------------------------------------------
            |
            | Se guardan como texto porque pueden contener:
            | - Números largos
            | - Letras
            | - Espacios
            | - Notación científica
            | - Valores como "- -"
            */

            $table->string('referencia_1', 255)->nullable();
            $table->string('referencia_2', 255)->nullable();
            $table->string('referencia_3', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Valor
            |--------------------------------------------------------------------------
            */

            $table->string('moneda', 10)->default('COP');

            $table->decimal('valor', 18, 2);

            $table->enum('tipo_movimiento', [
                'CREDITO',
                'DEBITO',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Valores originales
            |--------------------------------------------------------------------------
            |
            | Útiles para auditoría y para revisar errores de conversión.
            */

            $table->string('fecha_original')->nullable();
            $table->string('valor_original')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Control
            |--------------------------------------------------------------------------
            */

            $table->string('hash_movimiento', 64)->nullable()->index();

            $table->boolean('procesado')->default(false);
            $table->dateTime('fecha_procesado')->nullable();

            $table->text('observaciones')->nullable();

            $table->foreignId('usuario_importacion_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('fecha_movimiento');
            $table->index('tipo_movimiento');
            $table->index('descripcion_movimiento');
            $table->index(['fecha_movimiento', 'valor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_bancarios');
    }
};