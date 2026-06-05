{{-- Tablero de estación compartido por cocina y bar. Espera $title y usa
     $this->columns / $this->start() / $this->ready() del componente. --}}
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $title }}</flux:heading>
        <flux:button wire:click="refreshBoard" icon="arrow-path" variant="ghost" size="sm">Actualizar</flux:button>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach (['pendiente' => 'Pendientes', 'en_preparacion' => 'En preparación', 'listo' => 'Listos'] as $key => $label)
            <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-2.5 dark:border-zinc-700">
                    <span class="font-semibold">{{ $label }}</span>
                    <flux:badge size="sm">{{ $this->columns[$key]->count() }}</flux:badge>
                </div>

                <div class="flex-1 space-y-3 p-3">
                    @forelse ($this->columns[$key] as $item)
                        <div wire:key="item-{{ $item->id }}"
                             class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span class="font-medium">Mesa {{ $item->order->mesa->numero }} · {{ $item->order->participant?->nombre ?? '—' }}</span>
                                <span>{{ $item->created_at->format('H:i') }}</span>
                            </div>
                            <p class="mt-1 font-medium">{{ $item->quantity }}× {{ $item->product_name }}</p>
                            @if ($item->notes)
                                <p class="mt-0.5 text-xs font-medium text-amber-600 dark:text-amber-400">⚠ {{ $item->notes }}</p>
                            @endif

                            <div class="mt-3">
                                @if ($key === 'pendiente')
                                    <flux:button wire:click="start({{ $item->id }})" size="sm" variant="primary" class="w-full">Empezar</flux:button>
                                @elseif ($key === 'en_preparacion')
                                    <flux:button wire:click="ready({{ $item->id }})" size="sm" variant="primary" class="w-full">Marcar listo</flux:button>
                                @else
                                    <flux:badge color="green" size="sm" class="w-full justify-center">Listo · esperando mesero</flux:badge>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-2 py-8 text-center text-sm text-zinc-400">Sin ítems</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
