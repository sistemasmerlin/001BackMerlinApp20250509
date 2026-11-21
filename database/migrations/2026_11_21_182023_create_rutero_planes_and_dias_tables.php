<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutero_planes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users');
            $table->date('periodo');
            $table->string('estado', 20)->default('borrador');
            $table->timestamps();

            $table->unique(['asesor_id', 'periodo']);
        });

        Schema::create('rutero_dias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')
                ->constrained('rutero_planes')
                ->cascadeOnDelete();

            $table->date('fecha');
            $table->unsignedTinyInteger('dia_semana');
            $table->boolean('habilitado');
            $table->timestamps();

            $table->unique(['plan_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutero_dias');
        Schema::dropIfExists('rutero_planes');
    }
};