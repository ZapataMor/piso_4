<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Participantes de una sesión. El cliente solo ingresa su nombre; el
 * 'token' lo identifica en su dispositivo (cookie) sin login ni cuenta.
 * is_host marca al primero que escaneó (puede solicitar la cuenta).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_session_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('token', 64)->unique();
            $table->boolean('is_host')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('restaurant_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_participants');
    }
};
