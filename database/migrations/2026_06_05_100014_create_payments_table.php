<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pago individual de una cuenta. Una cuenta puede tener varios pagos
 * (división automática/personalizada). metodo: efectivo|transferencia|
 * tarjeta. estado: pago_pendiente|pago_confirmado|cancelado (la
 * confirmación la hace el personal manualmente). Para transferencia se
 * guardan nombre/teléfono del pagador (datos del mensaje de WhatsApp).
 * NO se almacenan comprobantes: el comprobante va por WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_participant_id')->nullable()
                ->constrained()->nullOnDelete();          // quién paga (null = pagador único externo)
            $table->string('metodo');                      // efectivo | transferencia | tarjeta
            $table->string('estado')->default('pago_pendiente'); // pago_pendiente | pago_confirmado | cancelado
            $table->decimal('monto', 12, 2)->default(0);
            $table->string('payer_nombre')->nullable();    // transferencia
            $table->string('payer_telefono')->nullable();  // transferencia
            $table->string('reference')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['bill_id', 'estado']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
