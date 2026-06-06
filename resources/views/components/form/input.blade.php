@props([
    'label' => null,
    'name',
    'type' => 'text',
    'placeholder' => null,
    'error' => false,
    'hint' => null,
])

<div class="space-y-2">
    @if ($label)
        <label for="{{ $name }}" class="text-sm font-medium text-zinc-300">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'input-base']) }}
        @if ($error) aria-invalid="true" @endif
    />

    @if ($error)
        <p class="text-xs text-red-400 font-medium">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-muted-sm">{{ $hint }}</p>
    @endif
</div>
