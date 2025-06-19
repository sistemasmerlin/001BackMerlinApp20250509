<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('prefijo', 10)->nullable()->after('id');
            $table->string('nota', 2000)->nullable()->after('observaciones');
            $table->string('orden_compra', 35)->nullable()->after('nota');
            $table->string('id_estado_pedido', 10)->nullable()->after('orden_compra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            //
        });
    }
};
