<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pqrs_responsables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->index();
            $table->json('correos')->nullable(); // ["a@..", "b@.."]
            $table->unsignedSmallInteger('sla_dias_default')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->unsignedSmallInteger('orden')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nombre', 'deleted_at'], 'uq_resp_nombre_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_responsables');
    }
};
