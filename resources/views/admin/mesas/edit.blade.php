<x-layouts::app :title="__('Editar mesa #').$mesa->numero">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div class="flex items-center gap-3">
            <flux:button :href="route('admin.mesas.index')" icon="arrow-left" variant="ghost" size="sm" wire:navigate />
            <flux:heading size="xl">Mesa #{{ $mesa->numero }}</flux:heading>
            <flux:badge :color="$mesa->estado->color()">{{ $mesa->estado->label() }}</flux:badge>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" variant="success" class="!my-0">
                <flux:callout.text>{{ session('status') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-6 lg:grid-cols-5">
            {{-- Formulario --}}
            <form method="POST" action="{{ route('admin.mesas.update', $mesa) }}"
                  class="flex flex-col gap-6 rounded-xl border border-zinc-200 p-6 lg:col-span-3 dark:border-zinc-700">
                @csrf
                @method('PUT')
                @include('admin.mesas._form')

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" icon="check">Guardar cambios</flux:button>
                </div>
            </form>

            {{-- Panel QR --}}
            <div class="flex flex-col items-center gap-4 rounded-xl border border-zinc-200 p-6 lg:col-span-2 dark:border-zinc-700">
                <flux:heading size="lg">Código QR</flux:heading>
                <div class="rounded-lg bg-white p-3">
                    {!! $qrSvg !!}
                </div>

                <flux:text class="break-all text-center text-xs text-zinc-500">{{ $mesa->public_url }}</flux:text>

                <div class="flex w-full flex-col gap-2">
                    <flux:button :href="route('admin.mesas.qr', $mesa)" icon="arrow-down-tray" variant="primary" class="w-full">
                        Descargar QR (SVG)
                    </flux:button>

                    <form method="POST" action="{{ route('admin.mesas.regenerate', $mesa) }}" class="w-full"
                          onsubmit="return confirm('Regenerar el QR invalidará el código impreso anterior. ¿Continuar?')">
                        @csrf
                        <flux:button type="submit" icon="arrow-path" variant="ghost" class="w-full">
                            Regenerar QR
                        </flux:button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
