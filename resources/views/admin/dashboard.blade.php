<x-layouts::app :title="__('Panel de Administración')">
    <div class="flex flex-col gap-6">
        <div>
            <flux:heading size="xl">Panel de Administración</flux:heading>
            <flux:text class="mt-1">Hola, {{ auth()->user()->name }}. Tienes acceso total al sistema.</flux:text>
        </div>

        <flux:callout icon="information-circle" variant="secondary">
            <flux:callout.heading>Dashboard administrativo</flux:callout.heading>
            <flux:callout.text>
                Las estadísticas, gestión de usuarios, productos y ventas se construyen en las fases 15 y 16.
                Ya puedes administrar <flux:link :href="route('admin.mesas.index')">Mesas y QR</flux:link>.
            </flux:callout.text>
        </flux:callout>
    </div>
</x-layouts::app>
