# Ejemplos de Código — Piso Cuatro

Referencia rápida para implementar nuevas vistas y componentes siguiendo el sistema de diseño.

## 📝 Estructura básica de una vista

### Vista de Personal (Admin, Mesero, Cocina, Bar)

```blade
<?php

use SomeService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Título · Piso 4')] class extends Component
{
    #[Computed]
    public function data()
    {
        return collect([]);
    }

    public function action()
    {
        // Lógica
    }
}; ?>

<div class="flex flex-col gap-8">
    <div>
        <p class="header-subtitle">Categoría</p>
        <div class="flex items-center justify-between">
            <h1 class="header-title">Título principal</h1>
            <button wire:click="refresh" class="btn-secondary">
                ↻ Actualizar
            </button>
        </div>
    </div>

    <!-- Contenido -->
    <x-dashboard.section title="Sección" :count="$count" variant="default">
        <!-- Items -->
    </x-dashboard.section>
</div>
```

### Vista de Cliente (Mesa)

```blade
<?php

use App\Models\Mesa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('layouts.customer')] #[Title('Página · Piso 4')] class extends Component
{
    public Mesa $mesa;

    public function mount(Mesa $mesa): void
    {
        $this->mesa = $mesa;
    }
}; ?>

<div class="relative flex min-h-svh flex-col">
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-5 py-4 backdrop-blur">
        <div>
            <p class="header-subtitle">Mesa {{ $mesa->numero }}</p>
            <h1 class="text-lg font-semibold text-zinc-100">Título</h1>
        </div>
        <div class="flex items-center gap-2">
            <button class="btn-secondary">Botón</button>
        </div>
    </header>

    <main class="flex-1 space-y-6 px-5 py-6">
        <!-- Contenido -->
    </main>
</div>
```

## 🎨 Ejemplos de componentes

### Métrica Card

```blade
<x-dashboard.metric-card 
    label="Ventas hoy"
    value="$5,234"
    icon="currency-dollar"
    variant="success"
/>
```

Variantes de color:
```blade
<!-- Verde - Éxito -->
<x-dashboard.metric-card ... variant="success" />

<!-- Ámbar - Advertencia -->
<x-dashboard.metric-card ... variant="warning" />

<!-- Rojo - Error -->
<x-dashboard.metric-card ... variant="error" />

<!-- Azul - Información -->
<x-dashboard.metric-card ... variant="info" />

<!-- Gris - Por defecto -->
<x-dashboard.metric-card ... variant="default" />
```

### Sección con Items

```blade
<x-dashboard.section 
    title="Órdenes pendientes"
    icon="fire"
    :count="$orders->count()"
    variant="warning"
>
    @forelse ($orders as $order)
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-800 p-3">
            <div class="min-w-0">
                <p class="font-medium text-zinc-100">Order #{{ $order->id }}</p>
                <p class="text-xs text-muted">{{ $order->created_at->format('H:i') }}</p>
            </div>
            <button wire:click="start({{ $order->id }})" class="btn-primary text-sm">
                Empezar
            </button>
        </div>
    @empty
        <div class="flex items-center justify-center py-12 text-muted">
            <p class="text-sm">Sin órdenes pendientes</p>
        </div>
    @endforelse
</x-dashboard.section>
```

### Grid de Cards

```blade
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @php($metrics = [
        ['Ventas', '$5,234', 'currency-dollar', 'success'],
        ['Mesas', '12', 'rectangle-group', 'info'],
        ['Pendientes', '8', 'fire', 'warning'],
        ['Pedidos', '23', 'receipt-percent', 'default'],
    ])
    @foreach ($metrics as [$label, $value, $icon, $variant])
        <x-dashboard.metric-card 
            :label="$label"
            :value="$value"
            :icon="$icon"
            :variant="$variant"
        />
    @endforeach
</div>
```

## 🔘 Botones

### Botón Primario

```blade
<!-- Largo -->
<button class="btn-primary">Guardar</button>

<!-- Corto -->
<button class="btn-primary px-3 py-1.5 text-sm">Ok</button>

<!-- Con ícono -->
<button class="btn-primary flex items-center gap-2">
    <flux:icon icon="plus" class="size-4" />
    Nuevo
</button>

<!-- Componente reutilizable -->
<x-btn.primary size="lg">
    Crear
</x-btn.primary>

<!-- Cargando -->
<x-btn.primary :loading="true">
    Guardando...
</x-btn.primary>
```

### Botón Secundario

```blade
<button class="btn-secondary">Cancelar</button>
```

### Botón Ghost

```blade
<button class="btn-ghost">Más opciones</button>
```

## 📋 Formularios

### Input básico

```blade
<div class="space-y-2">
    <label class="text-sm font-medium text-zinc-300">Email</label>
    <input type="email" class="input-base" placeholder="user@example.com">
</div>
```

### Componente Input reutilizable

```blade
<x-form.input
    name="email"
    label="Email"
    type="email"
    placeholder="user@example.com"
    hint="Usa tu email corporativo"
/>

<!-- Con error -->
<x-form.input
    name="email"
    label="Email"
    type="email"
    :error="$errors->first('email')"
/>
```

### Select

```blade
<select class="input-base">
    @foreach ($options as $option)
        <option value="{{ $option->id }}">{{ $option->name }}</option>
    @endforeach
</select>
```

### Textarea

```blade
<textarea class="input-base resize-none" rows="4" placeholder="Escribe..."></textarea>
```

### Checkbox

```blade
<label class="flex items-center gap-2 text-sm text-zinc-300 cursor-pointer">
    <input type="checkbox" class="rounded border-zinc-700 bg-zinc-900 text-amber-500">
    <span>Acepto los términos</span>
</label>
```

## 📦 Modales y Drawers

### Modal centrado

```blade
@if ($showModal)
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/70">
        <div class="w-full max-w-sm rounded-2xl border border-zinc-800 bg-zinc-900 p-6 space-y-5">
            <h3 class="text-xl font-semibold">Título</h3>
            
            <!-- Contenido -->
            
            <div class="grid grid-cols-2 gap-3">
                <button wire:click="cancel" class="btn-secondary">Cancelar</button>
                <button wire:click="confirm" class="btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
@endif
```

### Drawer (desde abajo)

```blade
@if ($showDrawer)
    <div class="fixed inset-0 z-40 bg-black/70" wire:click="close"></div>
    <div class="fixed inset-x-0 bottom-0 z-50 mx-auto max-h-[88vh] max-w-lg flex-col rounded-t-2xl border-t border-zinc-800 bg-zinc-900">
        <div class="border-b border-zinc-800 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Título</h3>
            <button wire:click="$set('showDrawer', false)">✕</button>
        </div>
        
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <!-- Contenido scrolleable -->
        </div>
        
        <div class="border-t border-zinc-800 px-6 py-4">
            <button class="btn-primary w-full">Confirmar</button>
        </div>
    </div>
@endif
```

## 🔄 Tableros Reactivos (Cocina/Bar)

### Columna Kanban

```blade
@php
    $states = ['pendiente', 'en_preparacion', 'listo'];
    $labels = ['Pendientes', 'En prep', 'Listos'];
    $colors = [
        'pendiente' => 'border-red-900 bg-red-950/30',
        'en_preparacion' => 'border-amber-900 bg-amber-950/30',
        'listo' => 'border-green-900 bg-green-950/30',
    ];
@endphp

<div class="grid gap-4 lg:grid-cols-3">
    @foreach ($states as $i => $state)
        <div class="rounded-xl border {{ $colors[$state] }} overflow-hidden">
            <div class="border-b border-inherit px-4 py-3 bg-zinc-900/50 flex items-center justify-between">
                <span class="font-semibold">{{ $labels[$i] }}</span>
                <span class="bg-zinc-800 px-2.5 py-0.5 rounded-full text-xs font-bold">
                    {{ $items->where('state', $state)->count() }}
                </span>
            </div>
            
            <div class="space-y-3 p-3">
                @forelse ($items->where('state', $state) as $item)
                    <div class="rounded-lg border border-zinc-700 bg-zinc-900 p-4">
                        <!-- Item content -->
                    </div>
                @empty
                    <p class="text-center py-12 text-muted">Sin ítems</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
```

## 🔔 Notificaciones y Estados

### Badge de estado

```blade
<!-- Verde - Éxito -->
<span class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-semibold text-green-300">
    ✓ Confirmado
</span>

<!-- Ámbar - Pendiente -->
<span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-300">
    Pendiente
</span>

<!-- Rojo - Error -->
<span class="rounded-full bg-red-500/20 px-3 py-1 text-xs font-semibold text-red-300">
    ✕ Cancelado
</span>
```

### Notificación (usar Flux)

```blade
{{-- En el componente Livewire --}}
Flux::toast(
    text: 'Operación exitosa',
    variant: 'success',
    duration: 1500
);
```

## 🌐 Tiempo Real (Reverb)

### Listener en componente

```php
<?php

use Livewire\Attributes\On;
use Livewire\Component;

class MyComponent extends Component
{
    #[On('echo-private:kitchen,.order.placed')]
    #[On('echo-private:kitchen,.order.item.status')]
    public function onOrderUpdate(): void
    {
        // Refresca los datos
        unset($this->orders);
    }
}
```

### Alpine + Reverb en Blade

```blade
<div x-data x-init="window.Echo && window.Echo.channel('mesa.{{ $mesa->qr_token }}').listen('.order.item.status', () => location.reload())">
    <!-- El div se recargará cuando haya cambios -->
</div>
```

## 🎯 Patrones comunes

### Lista con acciones

```blade
<div class="space-y-2">
    @forelse ($items as $item)
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-800 p-3">
            <div class="min-w-0">
                <p class="font-medium">{{ $item->name }}</p>
                <p class="text-xs text-muted">{{ $item->meta }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="edit({{ $item->id }})" class="btn-ghost text-sm">
                    Editar
                </button>
                <button wire:click="delete({{ $item->id }})" class="text-red-400 hover:text-red-300">
                    ✕
                </button>
            </div>
        </div>
    @empty
        <p class="text-center py-8 text-muted">Sin items</p>
    @endforelse
</div>
```

### Tabla simple (con scroll en mobile)

```blade
<div class="overflow-x-auto rounded-xl border border-zinc-800">
    <table class="w-full text-sm">
        <thead class="border-b border-zinc-800 bg-zinc-900/50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Columna 1</th>
                <th class="px-4 py-3 text-left font-semibold">Columna 2</th>
                <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800">
            @foreach ($rows as $row)
                <tr class="hover:bg-zinc-900/50">
                    <td class="px-4 py-3">{{ $row->col1 }}</td>
                    <td class="px-4 py-3">{{ $row->col2 }}</td>
                    <td class="px-4 py-3 text-right">
                        <button wire:click="edit({{ $row->id }})" class="btn-ghost text-sm">
                            Editar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

---

**Tip**: Usa `ctrl+f` en este archivo para buscar ejemplos específicos. Todos los componentes son reutilizables e importables con `<x-...>`.
