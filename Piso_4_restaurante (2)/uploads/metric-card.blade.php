@props([
    'label',
    'value',
    'icon' => null,
    'variant' => 'default', // default, success, warning, error, info
])

@php
    $variants = [
        'default' => 'text-amber-400',
        'success' => 'text-green-400',
        'warning' => 'text-amber-400',
        'error' => 'text-red-400',
        'info' => 'text-blue-400',
    ];
    $borderVariants = [
        'default' => 'border-amber-900 bg-amber-500/10',
        'success' => 'border-green-900 bg-green-500/10',
        'warning' => 'border-amber-900 bg-amber-500/10',
        'error' => 'border-red-900 bg-red-500/10',
        'info' => 'border-blue-900 bg-blue-500/10',
    ];
    $textColor = $variants[$variant] ?? $variants['default'];
    $borderClass = $borderVariants[$variant] ?? $borderVariants['default'];
@endphp

<div class="card-base">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-muted mb-1.5">{{ $label }}</p>
            <p class="serif text-3xl font-medium text-[var(--piso-fg)]">{{ $value }}</p>
        </div>
        @if ($icon)
            <div class="flex items-center justify-center">
                @switch($icon)
                    @case('currency-dollar')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6 {{ $textColor }}">
                            <path d="M12 1v22m-7-15h14c1.1 0 2 .9 2 2v6c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2v-6c0-1.1.9-2 2-2z"/>
                        </svg>
                        @break
                    @case('rectangle-group')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6 {{ $textColor }}">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        @break
                    @case('fire')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6 {{ $textColor }}">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.287-2.26.9-3.2"/>
                        </svg>
                        @break
                    @case('receipt-percent')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6 {{ $textColor }}">
                            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M8 9h.01M8 13h.01M16 9l-2 3 2 3"/>
                        </svg>
                        @break
                    @default
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6 {{ $textColor }}">
                            <circle cx="12" cy="12" r="1"/><path d="M12 1v6m0 6v6"/>
                        </svg>
                @endswitch
            </div>
        @endif
    </div>
</div>
