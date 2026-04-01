<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->string('pdf_unificado_disk')->nullable()->after('estado');
            $table->string('pdf_unificado_path')->nullable()->after('pdf_unificado_disk');
            $table->string('pdf_unificado_nombre')->nullable()->after('pdf_unificado_path');

            $table->string('auco_code')->nullable()->after('pdf_unificado_nombre');
            $table->string('auco_package')->nullable()->after('auco_code');
            $table->string('auco_status')->nullable()->after('auco_package');
            $table->longText('auco_response')->nullable()->after('auco_status');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn([
                'pdf_unificado_disk',
                'pdf_unificado_path',
                'pdf_unificado_nombre',
                'auco_code',
                'auco_package',
                'auco_status',
                'auco_response',
            ]);
        });
    }
};
