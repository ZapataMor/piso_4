@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center gap-2">
    <h1 class="serif text-3xl font-medium text-[var(--piso-fg)]">{{ $title }}</h1>
    <p class="text-sm font-light text-muted">{{ $description }}</p>
</div>
