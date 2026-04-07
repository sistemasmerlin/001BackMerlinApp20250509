<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrs', function (Blueprint $table) {
            $table->boolean('enviado_otro_usuario')
                ->default(0)
                ->after('cerrado_por');
        });
    }

    public function down(): void
    {
        Schema::table('pqrs', function (Blueprint $table) {
            $table->dropColumn('enviado_otro_usuario');
        });
    }
};
