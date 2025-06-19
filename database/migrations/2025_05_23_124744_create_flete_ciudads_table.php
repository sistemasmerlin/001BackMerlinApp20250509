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
        Schema::create('flete_ciudads', function (Blueprint $table) {
            $table->id();
    
            $table->string('depto');
            $table->string('cod_depto');
            $table->string('ciudad');
            $table->string('cod_ciudad');
            $table->decimal('menor', 10, 2);
            $table->decimal('mayor', 10, 2);
            $table->bigInteger('minimo'); // si es código o string
            $table->integer('entrega');
            $table->bigInteger('monto');
            $table->bigInteger('monto_minimo');
    
            $table->boolean('estado')->default(true);
            $table->boolean('eliminado')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flete_ciudads');
    }
};
