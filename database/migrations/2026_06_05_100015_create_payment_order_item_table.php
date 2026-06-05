<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivote para la DIVISIÓN PERSONALIZADA: asigna explícitamente qué
 * order_items cubre cada pago. (Las modalidades "única" y "automática"
 * no necesitan este pivote; se derivan del total o por participante.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_order_item', function (Blueprint $table) {
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->primary(['payment_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_order_item');
    }
};
