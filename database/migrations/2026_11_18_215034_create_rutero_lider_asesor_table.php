<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutero_lider_asesor', function (Blueprint $table): void {
            $table->foreignId('lider_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('asesor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['lider_id', 'asesor_id']);
            $table->index('asesor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutero_lider_asesor');
    }
};