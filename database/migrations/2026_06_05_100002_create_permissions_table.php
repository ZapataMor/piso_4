<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permisos granulares (ej: orders.view, mesas.manage, stats.view).
 * Se asocian a roles mediante la tabla pivote permission_role.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();           // orders.view, mesas.manage, ...
            $table->string('name');
            $table->string('group')->nullable();        // agrupación para la UI de admin
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
