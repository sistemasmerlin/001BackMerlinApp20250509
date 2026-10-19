<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_empaques', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 100)->unique();
            $table->string('descripcion');
            $table->unsignedInteger('unidades_por_empaque');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_empaques');
    }
};