<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('pqrs_causales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submotivo_id')->constrained('pqrs_submotivos');
            $table->foreignId('responsable_id')->constrained('pqrs_responsables');

            $table->string('nombre')->index();

            $table->boolean('requiere_adjunto')->default(false)->index();
            $table->boolean('permite_recogida')->default(false)->index();
            $table->unsignedSmallInteger('sla_dias')->nullable();

            $table->boolean('activo')->default(true)->index();
            $table->unsignedSmallInteger('orden')->default(0)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['submotivo_id', 'nombre', 'deleted_at'], 'uq_causal_submotivo_nombre_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_causales');
    }
};
