<x-layouts::customer :title="__('Bienvenido · Mesa ').$mesa->numero">
    <div class="flex flex-1 flex-col justify-center gap-8 px-6 py-10">
        <div class="text-center">
            <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro" class="mx-auto h-20 w-auto" />
            <p class="mt-6 text-sm uppercase tracking-[0.3em] text-amber-400/80">Mesa {{ $mesa->numero }}</p>
            <h1 class="mt-2 text-2xl font-semibold">Bienvenido a Piso Cuatro</h1>
            <p class="mt-2 text-sm text-zinc-400">Ingresa tu nombre para empezar tu pedido.</p>
        </div>

        <form method="POST" action="{{ route('mesa.join', $mesa) }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <input
                    name="nombre"
                    value="{{ old('nombre') }}"
                    placeholder="Tu nombre"
                    autocomplete="given-name"
                    autofocus
                    class="w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-lg text-zinc-100 placeholder-zinc-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400" />
                @error('nombre')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-amber-500 px-4 py-3 text-lg font-semibold text-zinc-950 transition hover:bg-amber-400">
                Ver el menú
            </button>
        </form>

        <p class="text-center text-xs text-zinc-600">
            No necesitas cuenta. Sesión {{ $session->codigo }}.
        </p>
    </div>
</x-layouts::customer>
