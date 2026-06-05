<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La cuenta (bill) de una sesión. Se crea al "Solicitar Cuenta".
 * modalidad: unica | automatica | personalizada. El total es la suma de
 * los order_items (sin propina ni IVA por ahora, según lo acordado).
 * Relación 1:1 con la sesión (unique en restaurant_session_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_participant_id')->nullable()
                ->constrained('session_participants')->nullOnDelete();
            $table->string('modalidad')->default('unica');  // unica | automatica | personalizada
            $table->string('estado')->default('solicitada'); // solicitada | en_pago | pagada | cerrada | cancelada
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
