<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pqrs', function (Blueprint $table) {
            $table->id();

            // ===== Identificación cliente =====
            $table->string('nit', 30)->index();
            $table->string('razon_social', 255)->nullable();

            // ===== Ubicación / contacto =====
            $table->string('departamento', 120)->nullable();
            $table->string('ciudad', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 50)->nullable();

            // ===== Correos =====
            $table->string('correo_cliente', 150)->nullable();
            $table->string('correo_asesor', 150)->nullable();

            // ===== Asesor =====
            $table->string('cod_asesor', 30)->nullable()->index();
            $table->string('nombre_asesor', 150)->nullable();

            // ===== Fechas flujo =====
            $table->timestamp('fecha_creacion')->nullable()->index(); // cuando el asesor la crea
            $table->timestamp('fecha_revisado')->nullable()->index(); // cuando inicia trámite
            $table->timestamp('fecha_cierre')->nullable()->index();   // cierre/anulación

            // ===== Estado (string porque puede variar) =====
            $table->string('estado', 50)->default('CREADA')->index();

            // ===== Comentarios =====
            $table->text('comentario_cierre')->nullable();

            // ===== Número OR(M) / relación futura =====
            $table->string('numero_orm', 50)->nullable()->index();     // visible para el usuario
            $table->unsignedBigInteger('orm_id')->nullable()->index(); // relación futura a pqrs_orms

            // ===== Acuerdos =====
            $table->string('tipo_acuerdo', 50)->nullable(); // aplica / no aplica (string)
            $table->text('nota_acuerdo')->nullable();
            $table->decimal('valor_acuerdo', 15, 2)->nullable();

            // ===== Auditoría =====
            $table->unsignedBigInteger('creado_por')->nullable()->index();   // user_id (sin FK por ahora)
            $table->unsignedBigInteger('revisado_por')->nullable()->index(); // user_id (sin FK por ahora)
            $table->unsignedBigInteger('cerrado_por')->nullable()->index();  // user_id (sin FK por ahora)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs');
    }
};
