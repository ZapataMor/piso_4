<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mesas físicas del restaurante. Cada mesa tiene un qr_token único
 * y aleatorio (NUNCA se expone el id en la URL pública /mesa/{token}).
 * Estado: disponible | ocupada | fuera_de_servicio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique();
            $table->string('nombre')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->string('estado')->default('disponible'); // disponible | ocupada | fuera_de_servicio
            $table->unsignedSmallInteger('capacidad')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
