<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pqrs_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pqrs_id')->constrained('pqrs')->cascadeOnDelete();
            $table->string('origen', 30)->default('factura'); // factura | general
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('path');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pqrs_id', 'origen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pqrs_adjuntos');
    }
};
