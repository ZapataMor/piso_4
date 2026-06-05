<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categorías del menú (capítulos): Entradas, Para Compartir, Pastas,
 * Platos Fuertes, Hamburguesas, Postres, Bebidas, Cócteles.
 * Refleja la estructura de public/piso-cuatro-menu/menu-data.js.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();               // entradas, bebidas, cocteles...
            $table->string('name');
            $table->string('kicker')->nullable();           // "Capítulo I · Para comenzar"
            $table->string('subtitle')->nullable();         // frase descriptiva (campo "sub")
            $table->string('photo')->nullable();            // ruta de imagen
            $table->string('bg')->nullable();               // smoke | bubbles (fondo)
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
