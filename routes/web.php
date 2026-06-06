<?php

use App\Http\Controllers\Customer\TableEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\EnsureParticipant;
use Illuminate\Support\Facades\Route;

// Menú público informativo (no requiere autenticación). NO modificar.
Route::view('/', 'welcome')->name('home');

// Punto de entrada que reenvía a cada usuario al panel de su rol.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

// Flujo público del cliente: escanear QR -> nombre -> menú interactivo.
Route::prefix('mesa/{mesa:qr_token}')->middleware('throttle:mesa-public')->group(function () {
    Route::get('/', [TableEntryController::class, 'show'])->name('mesa.show');
    Route::post('/entrar', [TableEntryController::class, 'join'])->middleware('throttle:mesa-join')->name('mesa.join');

    Route::middleware(EnsureParticipant::class)->group(function () {
        Route::livewire('/menu', 'pages::customer.menu')->name('mesa.menu');
        Route::livewire('/pedidos', 'pages::customer.orders')->name('mesa.orders');
        Route::livewire('/cuenta', 'pages::customer.bill')->name('mesa.bill');
    });
});

require __DIR__.'/admin.php';
require __DIR__.'/staff.php';
require __DIR__.'/settings.php';
