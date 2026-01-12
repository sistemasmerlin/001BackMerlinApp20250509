<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('categoria_asesor', 20)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Si vas a dropear la columna, primero quita el índice
            $table->dropIndex(['categoria_asesor']);
            $table->dropColumn('categoria_asesor');
        });
    }
};
