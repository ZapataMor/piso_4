<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesión de mesa: se abre cuando el primer cliente escanea el QR y se
 * cierra cuando se paga la cuenta. Regla: una mesa solo puede tener UNA
 * sesión 'activa' a la vez (se garantiza en SessionService dentro de una
 * transacción con lockForUpdate). 'codigo' es legible para humanos y se
 * usa en el mensaje de WhatsApp ("número de sesión").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
            $table->string('codigo')->unique();              // S-20260605-0001
            $table->string('estado')->default('activa');     // activa | cerrada
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();

            $table->index(['mesa_id', 'estado']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_sessions');
    }
};
