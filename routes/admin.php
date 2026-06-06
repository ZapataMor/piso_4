<?php

use App\Http\Controllers\Admin\MesaController;
use Illuminate\Support\Facades\Route;

/**
 * Panel de administración. Acceso exclusivo del rol admin.
 */
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
        Route::livewire('productos', 'pages::admin.products')->name('products');
        Route::livewire('categorias', 'pages::admin.categories')->name('categories');
        Route::livewire('usuarios', 'pages::admin.users')->name('users');
        Route::livewire('pedidos', 'pages::admin.orders')->name('orders');
        Route::livewire('estadisticas', 'pages::admin.statistics')->name('statistics');
        Route::livewire('configuracion', 'pages::admin.settings')->name('settings');

        // Mesas y QR (Fase 5)
        Route::middleware('permission:mesas.manage')->group(function () {
            Route::get('mesas/{mesa}/qr', [MesaController::class, 'downloadQr'])->name('mesas.qr');
            Route::post('mesas/{mesa}/qr/regenerate', [MesaController::class, 'regenerateQr'])->name('mesas.regenerate');
            Route::resource('mesas', MesaController::class)->except('show');
        });
    });
