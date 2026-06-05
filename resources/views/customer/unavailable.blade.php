<x-layouts::customer :title="__('Mesa no disponible')">
    <div class="flex flex-1 flex-col items-center justify-center gap-4 px-6 py-10 text-center">
        <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro" class="h-16 w-auto opacity-60" />
        <h1 class="mt-4 text-xl font-semibold">Mesa fuera de servicio</h1>
        <p class="text-sm text-zinc-400">
            La mesa {{ $mesa->numero }} no está disponible en este momento.
            Por favor solicita ayuda a un mesero.
        </p>
    </div>
</x-layouts::customer>
