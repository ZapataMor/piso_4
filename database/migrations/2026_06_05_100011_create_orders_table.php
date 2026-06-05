<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pedido confirmado. mesa_id está denormalizado (derivable de la sesión)
 * para acelerar las consultas de los tableros de cocina/bar/mesero.
 * 'estado' del pedido es un agregado de los estados de sus order_items.
 * 'numero' es un consecutivo legible dentro de la sesión (Pedido 1, 2...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('session_participant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
            $table->unsignedInteger('numero')->default(1);   // consecutivo dentro de la sesión
            $table->string('estado')->default('pendiente');  // pendiente|en_preparacion|listo|entregado|facturado|cancelado
            $table->decimal('subtotal', 12, 2)->default(0);  // suma cacheada de las líneas
            $table->string('notes')->nullable();
            $table->timestamp('placed_at')->nullable();      // hora de creación (confirmación)
            $table->timestamp('started_at')->nullable();     // hora de inicio de preparación
            $table->timestamp('ready_at')->nullable();       // hora en que quedó listo
            $table->timestamp('delivered_at')->nullable();   // hora de entrega
            $table->timestamps();

            $table->index(['estado']);
            $table->index(['mesa_id', 'estado']);
            $table->index(['restaurant_session_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
