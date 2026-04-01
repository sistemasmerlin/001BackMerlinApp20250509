<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_credito', function (Blueprint $table) {
            $table->id();

            $table->string('cod_depto', 10)->nullable();
            $table->string('depto', 120)->nullable();
            $table->string('cod_ciudad', 10)->nullable();
            $table->string('ciudad', 120)->nullable();
            $table->date('fecha_solicitud')->nullable();

            $table->string('razon_social')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('nit_cc', 30)->nullable();
            $table->string('representante_legal')->nullable();
            $table->string('identificacion_representante', 30)->nullable();
            $table->string('direccion_negocio')->nullable();
            $table->string('barrio')->nullable();
            $table->string('telefono_fijo', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('correo_electronico')->nullable();

            $table->string('contacto_compras')->nullable();
            $table->string('telefono_compras', 30)->nullable();
            $table->string('correo_compras')->nullable();

            $table->string('contacto_tesoreria')->nullable();
            $table->string('telefono_tesoreria', 30)->nullable();
            $table->string('correo_tesoreria')->nullable();

            $table->string('contacto_factura_electronica')->nullable();
            $table->string('telefono_factura_electronica', 30)->nullable();
            $table->string('correo_factura_electronica')->nullable();

            $table->boolean('rte_fuente')->default(false);
            $table->boolean('rte_iva')->default(false);
            $table->boolean('rte_ica')->default(false);

            $table->string('antiguedad_comercial', 100)->nullable();
            $table->string('tiempo_antiguedad')->nullable();
            $table->string('tipo_negocio')->nullable();
            $table->string('puntos_venta')->nullable();
            $table->string('canal_tradicional')->nullable();
            $table->string('canal_corporativo')->nullable();
            $table->string('numero_empleados')->nullable();

            $table->decimal('ventas_proyectadas_mes', 15, 2)->nullable();
            $table->decimal('cupo_sugerido', 15, 2)->nullable();

            $table->string('autorizacion_cod_depto', 10)->nullable();
            $table->string('autorizacion_depto', 120)->nullable();
            $table->string('autorizacion_cod_ciudad', 10)->nullable();
            $table->string('autorizacion_ciudad', 120)->nullable();
            $table->date('autorizacion_fecha')->nullable();
            $table->string('autorizacion_nombre_1')->nullable();
            $table->string('autorizacion_documento_1', 30)->nullable();
            $table->string('autorizacion_lugar_expedicion_1')->nullable();
            $table->string('autorizacion_razon_social')->nullable();
            $table->string('autorizacion_nit_cc', 30)->nullable();
            $table->string('autorizacion_nombre_2')->nullable();
            $table->string('autorizacion_documento_2', 30)->nullable();
            $table->string('autorizacion_lugar_expedicion_2')->nullable();
            $table->string('autorizacion_telefono_fijo', 30)->nullable();
            $table->string('autorizacion_celular', 30)->nullable();
            $table->string('autorizacion_correo')->nullable();
            $table->string('autorizacion_direccion')->nullable();

            $table->string('estado')->default('borrador');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['nit_cc']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_credito');
    }
};
