<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orms MODIFY estado VARCHAR(50) NOT NULL DEFAULT 'creada'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orms MODIFY estado VARCHAR(20) NOT NULL DEFAULT 'creada'");
    }
};
