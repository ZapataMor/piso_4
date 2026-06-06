<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="relative z-10">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
