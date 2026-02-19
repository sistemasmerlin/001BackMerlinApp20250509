<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrs_causales', function (Blueprint $table) {
            $table->tinyInteger('visible_asesor')->default(1)->after('activo');
            $table->index('visible_asesor');
        });
    }

    public function down(): void
    {
        Schema::table('pqrs_causales', function (Blueprint $table) {
            $table->dropIndex(['visible_asesor']);
            $table->dropColumn('visible_asesor');
        });
    }
};
