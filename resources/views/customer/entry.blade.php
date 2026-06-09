@php
    $participants = $participants ?? collect();
    $showPeoplePicker = $showPeoplePicker ?? false;
    $currentParticipant = $currentParticipant ?? null;
@endphp

<x-layouts::customer :title="__('Bienvenido · Mesa ').$mesa->numero">
    <div class="flex flex-1 flex-col justify-center gap-8 px-6 py-10 piso-in">
        <div class="text-center">
            <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro" class="mx-auto h-20 w-auto" />
            <span class="piso-rule mx-auto my-5 block w-32"></span>
            <p class="kicker">Mesa {{ $mesa->numero }}</p>
            <h1 class="serif mt-3 text-3xl font-medium text-[var(--piso-fg)]">Bienvenido a Piso Cuatro</h1>
            <p class="mt-2 text-sm font-light text-muted">
                {{ $showPeoplePicker ? 'Elige tu nombre si ya aparece, o registra a otra persona.' : 'Ingresa tu nombre para comenzar tu experiencia.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('mesa.join', $mesa) }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <input
                    name="nombre"
                    value="{{ old('nombre') }}"
                    placeholder="{{ $showPeoplePicker ? 'Nombre de la otra persona' : 'Tu nombre' }}"
                    autocomplete="given-name"
                    autofocus
                    class="input-base text-center text-lg" />
                @error('nombre')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-3.5">
                {{ $showPeoplePicker ? 'Agregar y pedir' : 'Ver el menú' }}
            </button>
        </form>

        @if (! $showPeoplePicker && $participants->isNotEmpty())
            <a href="{{ route('mesa.people', $mesa) }}" class="btn-secondary w-full justify-center py-3 text-center">
                ¿Alguien más va a pedir?
            </a>
        @endif

        @if ($showPeoplePicker)
            <section class="rounded-xl border border-zinc-800 bg-zinc-950/55 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-zinc-100">Personas de esta mesa</p>
                    <span class="text-xs text-muted">{{ $participants->count() }} registradas</span>
                </div>

                @if ($participants->isEmpty())
                    <p class="text-sm text-muted">Todavía no hay nombres registrados en esta mesa.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($participants as $participant)
                            @php($isCurrent = $currentParticipant?->id === $participant->id)

                            <form method="POST" action="{{ route('mesa.participants.use', [$mesa, $participant]) }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-3 text-left transition active:scale-[0.99] {{ $isCurrent ? 'border-[var(--piso-gold)] bg-[rgba(212,180,120,.12)] text-[var(--piso-fg)]' : 'border-zinc-800 bg-zinc-900/45 text-zinc-200 hover:border-zinc-700' }}">
                                    <span class="min-w-0 truncate font-medium">{{ $participant->nombre }}</span>
                                    <span class="shrink-0 text-xs font-semibold uppercase tracking-wide text-[var(--piso-gold)]">
                                        {{ $isCurrent ? 'Actual' : 'Usar' }}
                                    </span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        <p class="text-center text-muted-sm">
            No necesitas cuenta · Sesión {{ $session->codigo }}
        </p>
    </div>
</x-layouts::customer>
