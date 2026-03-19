<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pqrs_productos', function (Blueprint $table) {
            $table->text('notas')->nullable()->after('orm_comentario_revision');
        });
    }

    public function down(): void
    {
        Schema::table('pqrs_productos', function (Blueprint $table) {
            $table->dropColumn('notas');
        });
    }
};
