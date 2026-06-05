<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Productos del menú. tipo_preparacion enruta cada producto a
 * "cocina" o "bar". price es nullable a nivel de esquema (flexibilidad
 * para "precio de mercado"), aunque el seeder llena todos con valor.
 * group_label preserva los subgrupos del menú (ej: "Menú Infantil",
 * "Zumos Naturales"). softDeletes para no perder historial de pedidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();        // COP, sin centavos en la práctica
            $table->string('tipo_preparacion')->default('cocina'); // cocina | bar
            $table->string('group_label')->nullable();          // subgrupo dentro de la categoría
            $table->string('image')->nullable();
            $table->string('note')->nullable();                 // "Consultar disponibilidad"
            $table->boolean('is_available')->default(true);     // disponibilidad
            $table->boolean('is_featured')->default(false);     // destacado (feature)
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'display_order']);
            $table->index(['tipo_preparacion', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
