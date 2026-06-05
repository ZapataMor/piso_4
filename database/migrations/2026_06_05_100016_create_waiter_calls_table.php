<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Llamar Mesero": notificación inmediata para todos los meseros
 * conectados (broadcast por Reverb). mesa_id denormalizado para mostrar
 * el origen sin joins. estado: pendiente | atendido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiter_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_participant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
            $table->string('tipo')->default('llamado');     // llamado | cuenta
            $table->string('estado')->default('pendiente'); // pendiente | atendido
            $table->string('note')->nullable();
            $table->foreignId('attended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index(['mesa_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiter_calls');
    }
};
