<?php

use App\Http\Controllers\Admin\MesaController;
use Illuminate\Support\Facades\Route;

/**
 * Panel de administración. Acceso exclusivo del rol admin.
 * Las secciones (productos, usuarios, ventas...) se añaden en sus fases.
 */
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');

        // Mesas y QR (Fase 5)
        Route::middleware('permission:mesas.manage')->group(function () {
            Route::get('mesas/{mesa}/qr', [MesaController::class, 'downloadQr'])->name('mesas.qr');
            Route::post('mesas/{mesa}/qr/regenerate', [MesaController::class, 'regenerateQr'])->name('mesas.regenerate');
            Route::resource('mesas', MesaController::class)->except('show');
        });
    });
