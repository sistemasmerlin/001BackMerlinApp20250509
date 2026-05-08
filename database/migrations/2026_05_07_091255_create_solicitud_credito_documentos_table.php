<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_credito_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_credito_id')
                ->constrained('solicitudes_credito')
                ->cascadeOnDelete();

            $table->foreignId('tipo_documento_credito_id')
                ->constrained('tipos_documentos_credito');

            $table->string('nombre_original')->nullable();

            $table->string('archivo');

            $table->string('disk')->default('public');

            $table->string('mime_type')->nullable();

            $table->bigInteger('peso')->nullable();

            /*
                pendiente
                aprobado
                no_aprobado
            */
            $table->enum('estado', [
                'pendiente',
                'aprobado',
                'no_aprobado'
            ])->default('pendiente');

            $table->text('observacion')->nullable();

            $table->foreignId('aprobado_por')
                ->nullable()
                ->constrained('users');

            $table->timestamp('fecha_revision')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_credito_documentos');
    }
};