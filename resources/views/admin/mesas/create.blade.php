<x-layouts::app :title="__('Nueva mesa')">
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6">
        <div class="flex items-center gap-3">
            <flux:button :href="route('admin.mesas.index')" icon="arrow-left" variant="ghost" size="sm" wire:navigate />
            <flux:heading size="xl">Nueva mesa</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.mesas.store') }}" class="flex flex-col gap-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            @csrf
            @include('admin.mesas._form')

            <div class="flex justify-end gap-3">
                <flux:button :href="route('admin.mesas.index')" variant="ghost" wire:navigate>Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="qr-code">Crear y generar QR</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
