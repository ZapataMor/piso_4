<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('placed_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('estado');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('confirmed_at');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['placed_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['confirmed_at']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex(['requested_at']);
        });
    }
};
