<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased">
        <x-smoke-bg />
        <div class="relative z-10 flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="w-full max-w-sm flex flex-col gap-2 piso-in">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-4 font-medium" wire:navigate>
                    <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro" class="h-14 w-auto" />
                    <span class="kicker">Restaurante · Bar</span>
                </a>
                <span class="piso-rule mx-auto my-4 w-40"></span>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
