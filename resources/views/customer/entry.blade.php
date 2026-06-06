<x-layouts::customer :title="__('Bienvenido · Mesa ').$mesa->numero">
    <div class="flex flex-1 flex-col justify-center gap-8 px-6 py-10 piso-in">
        <div class="text-center">
            <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro" class="mx-auto h-20 w-auto" />
            <span class="piso-rule mx-auto my-5 block w-32"></span>
            <p class="kicker">Mesa {{ $mesa->numero }}</p>
            <h1 class="serif mt-3 text-3xl font-medium text-[var(--piso-fg)]">Bienvenido</h1>
            <p class="mt-2 text-sm font-light text-muted">Ingresa tu nombre para comenzar tu experiencia.</p>
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
                    class="input-base text-center text-lg" />
                @error('nombre')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-3.5">
                Ver el menú
            </button>
        </form>

        <p class="text-center text-muted-sm">
            No necesitas cuenta · Sesión {{ $session->codigo }}
        </p>
    </div>
</x-layouts::customer>
