{{-- Campos compartidos por create y edit. Espera $mesa (opcional) y $estados. --}}
@php($mesa = $mesa ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <flux:field>
        <flux:label>Número de mesa</flux:label>
        <flux:input name="numero" type="number" min="1" :value="old('numero', $mesa?->numero)" required />
        <flux:error name="numero" />
    </flux:field>

    <flux:field>
        <flux:label>Capacidad (opcional)</flux:label>
        <flux:input name="capacidad" type="number" min="1" :value="old('capacidad', $mesa?->capacidad)" />
        <flux:error name="capacidad" />
    </flux:field>

    <flux:field class="sm:col-span-2">
        <flux:label>Nombre (opcional)</flux:label>
        <flux:input name="nombre" :value="old('nombre', $mesa?->nombre)" placeholder="Ej: Terraza, Ventana, VIP…" />
        <flux:error name="nombre" />
    </flux:field>

    <flux:field>
        <flux:label>Estado</flux:label>
        <flux:select name="estado">
            @foreach ($estados as $estado)
                <flux:select.option
                    value="{{ $estado->value }}"
                    :selected="old('estado', $mesa?->estado->value ?? 'disponible') === $estado->value">
                    {{ $estado->label() }}
                </flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="estado" />
    </flux:field>
</div>
