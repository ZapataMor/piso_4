<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen">
        <x-smoke-bg />

        @php($navUser = auth()->user())

        <div class="app-shell">
            {{-- ===================== NAVBAR ===================== --}}
            <nav class="nav" x-data="{ userOpen: false }">
                <div class="nav__brand">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center">
                        <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro">
                    </a>
                    <span class="sep"></span>
                    <span class="tag">Piso<br>Cuatro</span>
                </div>

                <div class="nav__rail">
                    {{-- Platform --}}
                    <div class="nav__group">
                        <a href="{{ route('dashboard') }}" wire:navigate class="nav__item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5"/></svg>
                            <span class="lbl">Dashboard</span><span class="tip">Dashboard</span>
                        </a>
                    </div>

                    @if ($navUser?->isAdmin() || $navUser?->hasRole('cocina') || $navUser?->hasRole('bar') || $navUser?->hasRole('mesero'))
                        <div class="nav__divider"></div>
                        {{-- Operación --}}
                        <div class="nav__group">
                            @if ($navUser?->isAdmin() || $navUser?->hasRole('cocina'))
                                <a href="{{ route('kitchen.board') }}" wire:navigate class="nav__item {{ request()->routeIs('kitchen.board') ? 'active' : '' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.29-2.26.9-3.2"/></svg>
                                    <span class="lbl">Cocina</span><span class="tip">Cocina</span>
                                </a>
                            @endif
                            @if ($navUser?->isAdmin() || $navUser?->hasRole('bar'))
                                <a href="{{ route('bar.board') }}" wire:navigate class="nav__item {{ request()->routeIs('bar.board') ? 'active' : '' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 3h6M10 3v5.5L5.5 18a1.4 1.4 0 0 0 1.3 2h10.4a1.4 1.4 0 0 0 1.3-2L14 8.5V3"/><path d="M7.4 13h9.2"/></svg>
                                    <span class="lbl">Bar</span><span class="tip">Bar</span>
                                </a>
                            @endif
                            @if ($navUser?->isAdmin() || $navUser?->hasRole('mesero'))
                                <a href="{{ route('waiter.dashboard') }}" wire:navigate class="nav__item {{ request()->routeIs('waiter.dashboard') ? 'active' : '' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8a6 6 0 1 0-12 0c0 7-2 9-2 9h16s-2-2-2-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                                    <span class="lbl">Meseros</span><span class="tip">Meseros</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    @if ($navUser?->isAdmin())
                        <div class="nav__divider"></div>
                        {{-- Administración --}}
                        <div class="nav__group">
                            <a href="{{ route('admin.dashboard') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7.5" height="7.5" rx="1.4"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.4"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.4"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.4"/></svg>
                                <span class="lbl">Panel</span><span class="tip">Panel</span>
                            </a>
                            <a href="{{ route('admin.products') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 11h16v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><path d="M3 8.5a2.5 2.5 0 0 1 2.5-2.5h13A2.5 2.5 0 0 1 21 8.5V11H3z"/><path d="M12 6V4M9 6c0-1.5 3-1.5 3 0M15 6c0-1.5-3-1.5-3 0"/></svg>
                                <span class="lbl">Productos</span><span class="tip">Productos</span>
                            </a>
                            <a href="{{ route('admin.categories') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7.5A1.5 1.5 0 0 1 4.5 6h6.3a2 2 0 0 1 1.4.6l8 8a1.8 1.8 0 0 1 0 2.5l-5.1 5.1a1.8 1.8 0 0 1-2.5 0l-8-8A2 2 0 0 1 4 12.8z"/><circle cx="8" cy="11" r="1.4"/></svg>
                                <span class="lbl">Categorías</span><span class="tip">Categorías</span>
                            </a>
                            <a href="{{ route('admin.mesas.index') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.mesas.*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M20 17v4M17 20h4"/></svg>
                                <span class="lbl">Mesas y QR</span><span class="tip">Mesas y QR</span>
                            </a>
                            <a href="{{ route('admin.users') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3 3 0 0 1 0 5.6M17.5 20a5.5 5.5 0 0 0-2.8-4.8"/></svg>
                                <span class="lbl">Usuarios</span><span class="tip">Usuarios</span>
                            </a>
                            <a href="{{ route('admin.orders') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 4h8a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 0 1 2-2z"/><path d="M9 9h6M9 13h4"/></svg>
                                <span class="lbl">Pedidos</span><span class="tip">Pedidos</span>
                            </a>
                            <a href="{{ route('admin.statistics') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 20V4"/><path d="M4 20h16"/><path d="M8 17v-5M12.5 17V8M17 17v-8"/></svg>
                                <span class="lbl">Estadísticas</span><span class="tip">Estadísticas</span>
                            </a>
                            <a href="{{ route('admin.settings') }}" wire:navigate class="nav__item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 0 1-4 0v-.2A1.6 1.6 0 0 0 7 19.4a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3 14.9a1.6 1.6 0 0 0-1.5-1H1.4a2 2 0 0 1 0-4h.2A1.6 1.6 0 0 0 3 8.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 8.6 5 1.6 1.6 0 0 0 9.6 3.5V3.4a2 2 0 0 1 4 0v.2a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8 1.6 1.6 0 0 0 1.5 1h.1a2 2 0 0 1 0 4h-.2a1.6 1.6 0 0 0-1.5 1z"/></svg>
                                <span class="lbl">Configuración</span><span class="tip">Configuración</span>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="nav__utils">
                    <button type="button" class="icon-btn" onclick="window.open('https://github.com/laravel/livewire-starter-kit','_blank')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 19c-4.3 1.4-4.3-2.5-6-3m12 5v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1.1-.3-3.5 1.3a12 12 0 0 0-6.3 0C6.9 2.3 5.8 2.6 5.8 2.6a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.4 9c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V20"/></svg>
                        <span class="tip">Repository</span>
                    </button>
                    <button type="button" class="icon-btn" onclick="window.open('https://laravel.com/docs','_blank')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15.5H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20"/></svg>
                        <span class="tip">Documentation</span>
                    </button>

                    <div class="user" :class="{ open: userOpen }" @click.outside="userOpen = false">
                        <button type="button" class="user__btn" @click="userOpen = !userOpen">
                            <span class="user__av">{{ $navUser->initials() }}</span>
                            <span class="user__name">{{ $navUser->name }}</span>
                            <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="user__menu">
                            <div class="user__head">
                                <span class="user__av">{{ $navUser->initials() }}</span>
                                <div>
                                    <div class="nm">{{ $navUser->name }}</div>
                                    <div class="em">{{ $navUser->email }}</div>
                                </div>
                            </div>
                            <div class="user__rule"></div>
                            <a href="{{ route('profile.edit') }}" wire:navigate>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8M4.6 9a1.6 1.6 0 0 0-.3-1.8"/></svg>
                                {{ __('Settings') }}
                            </a>
                            <div class="user__rule"></div>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" data-test="logout-button">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                                    {{ __('Log out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            {{-- ===================== MAIN ===================== --}}
            <main class="app-main relative z-10">
                {{ $slot }}
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
