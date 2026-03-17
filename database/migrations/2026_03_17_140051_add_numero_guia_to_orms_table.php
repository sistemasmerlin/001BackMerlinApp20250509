<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orms', function (Blueprint $table) {
            $table->string('numero_guia', 100)->nullable()->after('transportadora_id');
        });
    }

    public function down(): void
    {
        Schema::table('orms', function (Blueprint $table) {
            $table->dropColumn('numero_guia');
        });
    }
};
