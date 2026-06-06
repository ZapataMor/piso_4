<?php

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn (User $user, $id) => (int) $user->id === (int) $id);

/*
 * Canales privados de las estaciones del personal (autenticados por rol;
 * el admin entra a todos). Los clientes NO tienen login: sus
 * actualizaciones viajan por un canal PÚBLICO mesa.{qr_token} (token
 * aleatorio e imposible de adivinar), por lo que no se declara aquí.
 */
Broadcast::channel('kitchen', fn (User $user) => $user->isAdmin() || $user->hasRole(RoleType::Cocina));
Broadcast::channel('bar', fn (User $user) => $user->isAdmin() || $user->hasRole(RoleType::Bar));
Broadcast::channel('waiters', fn (User $user) => $user->isAdmin() || $user->hasRole(RoleType::Mesero));
