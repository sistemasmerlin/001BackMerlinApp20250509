<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meli_eventos', function (Blueprint $table) {
            $table->id();

            $table->string('tipo', 30)->index();
            $table->string('metodo', 10)->nullable();
            $table->text('url')->nullable();

            $table->string('topic')->nullable()->index();
            $table->text('resource')->nullable();
            $table->string('user_id')->nullable()->index();
            $table->string('application_id')->nullable();

            $table->longText('query_params')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('raw_body')->nullable();
            $table->longText('headers')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('estado', 30)->default('RECIBIDO')->index();
            $table->text('error')->nullable();

            $table->timestamps();

            $table->index(['tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meli_eventos');
    }
};