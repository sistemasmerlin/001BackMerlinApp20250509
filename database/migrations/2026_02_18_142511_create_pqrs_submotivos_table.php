<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pqrs_submotivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motivo_id')->constrained('pqrs_motivos');
            $table->string('nombre')->index();
            $table->boolean('activo')->default(true)->index();
            $table->unsignedSmallInteger('orden')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['motivo_id', 'nombre', 'deleted_at'], 'uq_submotivo_motivo_nombre_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_submotivos');
    }
};
