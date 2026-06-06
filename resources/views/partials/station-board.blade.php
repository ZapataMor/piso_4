{{-- Tablero de estación compartido por cocina y bar. Espera $title y usa
     $this->columns / $this->start() / $this->ready() del componente. --}}
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4 piso-in">
        <div>
            <p class="header-subtitle mb-2">Estación</p>
            <h1 class="header-title">{{ $title }}</h1>
        </div>
        <button type="button" wire:click="refreshBoard" class="btn-secondary shrink-0">↻ Actualizar</button>
    </div>

    <div class="grid gap-4 lg:grid-cols-3 auto-rows-max">
        @foreach (['pendiente' => 'Pendientes', 'en_preparacion' => 'En preparación', 'listo' => 'Listos'] as $key => $label)
            @php
                $statusColors = [
                    'pendiente' => 'border-red-900 bg-red-950/30',
                    'en_preparacion' => 'border-amber-900 bg-amber-950/30',
                    'listo' => 'border-green-900 bg-green-950/30',
                ];
            @endphp
            <div class="flex flex-col rounded-xl border {{ $statusColors[$key] }} overflow-hidden">
                <div class="flex items-center justify-between border-b border-inherit px-4 py-3 bg-zinc-900/50">
                    <span class="font-semibold text-zinc-100">{{ $label }}</span>
                    <span class="inline-flex items-center justify-center rounded-full bg-zinc-800/50 px-2.5 py-0.5 text-xs font-bold text-amber-400 ring-1 ring-amber-400/30">
                        {{ $this->columns[$key]->count() }}
                    </span>
                </div>

                <div class="flex-1 space-y-3 p-3 min-h-96">
                    @forelse ($this->columns[$key] as $item)
                        <div wire:key="item-{{ $item->id }}" class="rounded-lg border border-zinc-700 bg-zinc-900 p-4 space-y-3 hover:border-amber-700 transition">
                            <div class="flex items-center justify-between gap-3 text-xs text-zinc-400">
                                <span class="font-semibold text-zinc-300">Mesa {{ $item->order->mesa->numero }}</span>
                                <span class="text-zinc-500">{{ $item->created_at->format('H:i') }}</span>
                            </div>

                            <div>
                                <p class="font-semibold text-zinc-100">{{ $item->quantity }}× {{ $item->product_name }}</p>
                                @if ($item->order->participant?->nombre)
                                    <p class="text-xs text-zinc-500 mt-1">{{ $item->order->participant->nombre }}</p>
                                @endif
                            </div>

                            @if ($item->notes)
                                <div class="rounded-lg bg-amber-950/40 border border-amber-800 p-2.5">
                                    <p class="text-xs font-medium text-amber-300">⚠ Nota: {{ $item->notes }}</p>
                                </div>
                            @endif

                            <div class="pt-2">
                                @if ($key === 'pendiente')
                                    <button type="button" wire:click="start({{ $item->id }})" class="w-full rounded-lg bg-amber-500 px-3 py-2.5 text-sm font-semibold text-zinc-950 hover:bg-amber-600 active:scale-[0.98] transition">
                                        Empezar a preparar
                                    </button>
                                @elseif ($key === 'en_preparacion')
                                    <button type="button" wire:click="ready({{ $item->id }})" class="w-full rounded-lg bg-green-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-green-700 active:scale-[0.98] transition">
                                        ✓ Marcar listo
                                    </button>
                                @else
                                    <div class="rounded-lg bg-green-950/50 border border-green-800 px-3 py-2.5 text-center">
                                        <p class="text-xs font-semibold text-green-400">✓ Listo para servir</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-80 text-zinc-500">
                            <p class="text-sm font-medium">Sin ítems por preparar</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
