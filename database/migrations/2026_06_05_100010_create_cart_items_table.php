<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carrito BORRADOR por participante. Se persiste en BD para sobrevivir
 * recargas/reconexiones en móvil. NO es un pedido: solo al pulsar
 * "Enviar Pedido" se convierte en order + order_items (OrderService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('session_participant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
