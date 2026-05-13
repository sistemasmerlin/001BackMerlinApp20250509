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
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->text('comentario_revision_documentos')->nullable();
            $table->timestamp('fecha_revision_documentos')->nullable();
            $table->foreignId('revision_documentos_por')->nullable()->constrained('users');

            $table->decimal('cupo_asignado', 15, 2)->nullable();
            $table->string('condicion_pago_aprobada')->nullable();
            $table->text('comentario_cierre_aprobado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('solicitudes_credito', function (Blueprint $table) {
        $table->dropForeign(['revision_documentos_por']);

        $table->dropColumn([
            'comentario_revision_documentos',
            'fecha_revision_documentos',
            'revision_documentos_por',
            'cupo_asignado',
            'condicion_pago_aprobada',
            'comentario_cierre_aprobado',
        ]);
    });
    }
};
