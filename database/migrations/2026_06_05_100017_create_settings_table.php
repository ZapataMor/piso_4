<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración key/value del restaurante: número de WhatsApp, datos
 * bancarios para transferencias, nombre del local, etc. 'type' indica
 * cómo castear el valor (string|integer|boolean|json).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string | integer | boolean | json
            $table->string('group')->nullable();       // agrupación en la UI de admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
