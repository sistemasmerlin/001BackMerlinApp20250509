<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pqrs_productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pqrs_id')->constrained('pqrs')->cascadeOnDelete();

            // ✅ normalizado
            $table->foreignId('causal_id')->nullable()->constrained('pqrs_causales');
            $table->foreignId('responsable_id')->nullable()->constrained('pqrs_responsables');
            $table->foreignId('submotivo_id')->nullable()->constrained('pqrs_submotivos'); // opcional si te sirve

            // datos del documento/producto (BI_T461_1)
            $table->string('tipo_docto', 10)->nullable();
            $table->string('nro_docto', 30)->nullable();
            $table->date('fecha')->nullable();

            $table->string('referencia', 50)->nullable();
            $table->string('descripcion_ref', 255)->nullable();

            // unidades solicitadas en la PQRS
            $table->decimal('unidades_solicitadas', 18, 4)->default(0);

            // valores (si quieres guardarlos por trazabilidad)
            $table->decimal('precio_unitario', 18, 4)->default(0);
            $table->decimal('valor_bruto', 18, 4)->default(0);
            $table->decimal('valor_imp', 18, 4)->default(0);
            $table->decimal('valor_neto', 18, 4)->default(0);

            // flags
            $table->boolean('requiere_recogida')->default(false);

            // 0=no, 1=pendiente, 2=aprobada, 3=rechazada
            $table->unsignedTinyInteger('solicitud_recogida')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['pqrs_id', 'referencia']);
            $table->index(['tipo_docto', 'nro_docto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_productos');
    }
    
};
