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
        Schema::create('integradores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nit')->unique();

            $table->string('nombre_comercial')->nullable();

            $table->string('prefijo_pedido', 10);

            $table->string('lista_precio', 10)->default('001');
            $table->string('id_sucursal', 10)->default('020');
            $table->string('punto_envio', 10)->default('000');
            $table->string('condicion_pago', 20)->default('30D');

            $table->string('codigo_asesor')->nullable();
            $table->string('nombre_asesor')->nullable();

            $table->string('correo_notificacion')->nullable();

            // Reglas de negocio
            $table->boolean('activo')->default(true);
            $table->boolean('calcula_flete')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integradores');
    }
};
