<?php

use Illuminate\Support\Facades\Route;

/**
 * Paneles operativos del personal. Cada rol tiene su entrada y ningún
 * rol accede a la de otro (el admin sí, por bypass). Tableros reactivos
 * en Livewire; el push en tiempo real (Reverb) se conecta en la Fase 14.
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:cocina')->group(function () {
        Route::livewire('cocina', 'pages::kitchen.board')->name('kitchen.board');
    });

    Route::middleware('role:bar')->group(function () {
        Route::livewire('bar', 'pages::bar.board')->name('bar.board');
    });

    Route::middleware('role:mesero')->group(function () {
        Route::livewire('mesero', 'pages::waiter.dashboard')->name('waiter.dashboard');
    });
});
