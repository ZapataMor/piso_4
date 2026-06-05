<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El personal (users) se vincula a un rol. is_active permite
 * deshabilitar cuentas sin borrarlas; phone para contacto interno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
            $table->string('phone')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['is_active', 'phone']);
        });
    }
};
