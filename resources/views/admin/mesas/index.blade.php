<x-layouts::app :title="__('Mesas y QR')">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Mesas y QR</flux:heading>
                <flux:text class="mt-1">Administra las mesas y sus códigos QR.</flux:text>
            </div>
            <flux:button :href="route('admin.mesas.create')" icon="plus" variant="primary" wire:navigate>
                Nueva mesa
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" variant="success" class="!my-0">
                <flux:callout.text>{{ session('status') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 font-medium">Capacidad</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($mesas as $mesa)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-semibold">{{ $mesa->numero }}</td>
                            <td class="px-4 py-3">{{ $mesa->nombre ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" :color="$mesa->estado->color()">{{ $mesa->estado->label() }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">{{ $mesa->capacidad ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" :href="route('admin.mesas.edit', $mesa)" icon="pencil-square" variant="ghost" wire:navigate>
                                        Editar
                                    </flux:button>
                                    <flux:button size="sm" :href="route('admin.mesas.qr', $mesa)" icon="arrow-down-tray" variant="ghost">
                                        QR
                                    </flux:button>
                                    <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}"
                                          onsubmit="return confirm('¿Eliminar la mesa #{{ $mesa->numero }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="sm" type="submit" icon="trash" variant="danger">Eliminar</flux:button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500">
                                Aún no hay mesas. Crea la primera con “Nueva mesa”.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
