@props([
    'title',
    'icon' => null,
    'count' => null,
    'variant' => 'default', // default, success, warning, error
])

@php
    $variants = [
        'default' => 'border-zinc-800 bg-zinc-900/50',
        'success' => 'border-green-900 bg-green-950/30',
        'warning' => 'border-amber-900 bg-amber-950/30',
        'error' => 'border-red-900 bg-red-950/40',
    ];
    $borderClass = $variants[$variant] ?? $variants['default'];

    $iconMap = [
        'bell-alert' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.8 4.3a9 9 0 0 0-12.6 0M6.14 14a3 3 0 0 1-1.4-5.9M20 17v2H4v-2M9 9h1v4H9M15 9h1v4h-1"/></svg>',
        'currency-dollar' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22m-7-15h14c1.1 0 2 .9 2 2v6c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2v-6c0-1.1.9-2 2-2z"/></svg>',
        'rectangle-group' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'fire' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.287-2.26.9-3.2"/></svg>',
        'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        'chart-bar' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    ];
@endphp

<div class="rounded-xl border {{ $borderClass }} overflow-hidden">
    <div class="flex items-center justify-between border-b border-inherit px-6 py-3.5 bg-zinc-900/50">
        <div class="flex items-center gap-2">
            @if ($icon && isset($iconMap[$icon]))
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5 text-zinc-400">
                    {!! $iconMap[$icon] !!}
                </svg>
            @elseif ($icon)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5 text-zinc-400"><circle cx="12" cy="12" r="1"/></svg>
            @endif
            <h3 class="text-sm font-semibold text-zinc-100">{{ $title }}</h3>
            @if ($count !== null)
                <span class="ml-2 inline-flex items-center justify-center rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-medium text-amber-400">
                    {{ $count }}
                </span>
            @endif
        </div>
        {{ $icon_slot ?? '' }}
    </div>
    <div class="space-y-2 p-4">
        {{ $slot }}
    </div>
</div>
