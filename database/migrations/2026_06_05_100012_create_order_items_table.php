<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Línea de pedido. Es la UNIDAD DE TRABAJO de cada estación: cada línea
 * lleva su propio 'estado' y se enruta por 'tipo_preparacion' (cocina/bar).
 * Se guardan snapshots (product_name, unit_price, tipo_preparacion) para
 * preservar la cuenta aunque el producto cambie o se elimine después.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');                   // snapshot
            $table->decimal('unit_price', 12, 2)->default(0); // snapshot
            $table->unsignedInteger('quantity')->default(1);
            $table->string('tipo_preparacion')->default('cocina'); // snapshot: cocina | bar
            $table->string('estado')->default('pendiente');   // pendiente|en_preparacion|listo|entregado|cancelado
            $table->string('notes')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['tipo_preparacion', 'estado']); // tableros por estación
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
