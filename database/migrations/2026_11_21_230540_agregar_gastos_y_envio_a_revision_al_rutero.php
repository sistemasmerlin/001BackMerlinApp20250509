<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutero_planes', function (Blueprint $table): void {
            $table->timestamp('enviado_revision_at')->nullable();
        });

        Schema::create('rutero_gastos', function (Blueprint $table): void {
            $table->foreignId('dia_id')
                ->constrained('rutero_dias')
                ->cascadeOnDelete();

            $table->primary('dia_id');
            $table->unsignedInteger('hospedaje')->default(0);
            $table->unsignedInteger('alimentacion')->default(0);
            $table->unsignedInteger('transporte_intermunicipal')->default(0);
            $table->unsignedInteger('transporte_urbano_combustible')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutero_gastos');

        Schema::table('rutero_planes', function (Blueprint $table): void {
            $table->dropColumn('enviado_revision_at');
        });
    }
};