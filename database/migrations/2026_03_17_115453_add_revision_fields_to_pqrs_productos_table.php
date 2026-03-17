<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrs_productos', function (Blueprint $table) {
            $table->unsignedBigInteger('revisado_por')->nullable()->after('estado');
            $table->timestamp('fecha_revision')->nullable()->after('revisado_por');
            $table->text('comentario_revision')->nullable()->after('fecha_revision');

            $table->string('estado_orm')->nullable()->after('comentario_revision');
            $table->unsignedBigInteger('orm_revisada_por')->nullable()->after('estado_orm');
            $table->timestamp('orm_fecha_revision')->nullable()->after('orm_revisada_por');
            $table->text('orm_comentario_revision')->nullable()->after('orm_fecha_revision');
        });
    }

    public function down(): void
    {
        Schema::table('pqrs_productos', function (Blueprint $table) {
            $table->dropColumn([
                'revisado_por',
                'fecha_revision',
                'comentario_revision',
                'estado_orm',
                'orm_revisada_por',
                'orm_fecha_revision',
                'orm_comentario_revision',
            ]);
        });
    }
};
