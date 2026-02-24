<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pqrs_producto_adjuntos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pqrs_producto_id')->constrained('pqrs_productos')->cascadeOnDelete();

            $table->string('original_name', 255);
            $table->string('mime', 150)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            // ruta en disk public
            $table->string('path', 500);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['pqrs_producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_producto_adjuntos');
    }

};
