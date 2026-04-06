<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orms', function (Blueprint $table) {
            $table->dateTime('fecha_recogida_transportadora')
                ->nullable()
                ->after('fecha_recogida_programada');

            $table->unsignedBigInteger('usuario_marca_recogida_transportadora_id')
                ->nullable()
                ->after('fecha_recogida_transportadora');

            $table->foreign('usuario_marca_recogida_transportadora_id', 'orms_usuario_marca_recogida_transportadora_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orms', function (Blueprint $table) {
            $table->dropForeign('orms_usuario_marca_recogida_transportadora_fk');
            $table->dropColumn([
                'fecha_recogida_transportadora',
                'usuario_marca_recogida_transportadora_id',
            ]);
        });
    }
};
