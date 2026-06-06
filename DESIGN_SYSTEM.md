# Piso Cuatro — Sistema de Diseño

## Paleta de Colores

### Tema Oscuro (Principal)
- **Fondo primario**: `zinc-950` (#0a0a0a) — fondo general
- **Fondo secundario**: `zinc-900` (#171717) — cards, inputs
- **Fondo terciario**: `zinc-800` (#262626) — borders, separadores
- **Accent primario**: `amber-500` (#f59e0b) — botones principales, acciones
- **Accent secundario**: `amber-400` (#fbbf24) — texto destacado, decoraciones
- **Texto primario**: `zinc-100` (#f5f5f5) — títulos, texto principal
- **Texto secundario**: `zinc-200` (#e5e5e5) — etiquetas
- **Texto terciario**: `zinc-400` (#a3a3a3) — placeholder, hints
- **Texto cuaternario**: `zinc-500` (#737373) — meta information

### Colores de Estado
- **Éxito**: `green-600` (#16a34a) para confirmaciones, items listos
- **Advertencia**: `amber-600` (#d97706) para pendientes, atención
- **Error**: `red-600` (#dc2626) para cancelados, llamadas urgentes
- **Info**: `blue-600` (#2563eb) para información general

## Tipografía

- **Font**: `Instrument Sans`, ui-sans-serif, system-ui
- **Tamaño base**: 16px (rem)

### Jerarquía de textos
1. **Header XL**: 24px (1.5rem), font-semibold
2. **Header LG**: 18px (1.125rem), font-semibold
3. **Body**: 14px (0.875rem), font-normal
4. **Small**: 12px (0.75rem), font-normal
5. **Tiny**: 11px (0.6875rem), font-normal

## Componentes

### Botones

#### Botón Primario (btn-primary)
```html
<button class="btn-primary">Acción principal</button>
```
- Background: `amber-500`
- Hover: `amber-600`
- Text: `zinc-950` (bold)

#### Botón Secundario (btn-secondary)
```html
<button class="btn-secondary">Acción secundaria</button>
```
- Background: `zinc-900`
- Border: `zinc-700`
- Text: `zinc-200`

#### Botón Ghost (btn-ghost)
```html
<button class="btn-ghost">Acción mínima</button>
```
- Background: transparent
- Hover: `zinc-800/50`
- Text: `zinc-300`

### Inputs

```html
<input class="input-base" type="text" placeholder="...">
<textarea class="input-base" placeholder="..."></textarea>
```
- Background: `zinc-900`
- Border: `zinc-700` → focus: `amber-400`
- Text: `zinc-100`

### Cards

```html
<div class="card-base">
  Contenido
</div>
```
- Background: `zinc-900`
- Border: `zinc-800`
- Padding: 1rem (4 * 4px)

## Layouts

### Layout del Cliente (Mesa)
- Fondo: `zinc-950`
- Max-width: 28rem (max-w-lg)
- Header sticky con border-b
- Main con espacio para fixed bottom bar

### Layouts de Personal (Admin, Cocina, Bar, Mesero)
- Fondo: `zinc-950` (full)
- Usa sidebar layout (via Flux)
- Grid responsivo para tarjetas

## Breakpoints

- Mobile: default (< 640px)
- SM: 640px
- MD: 768px
- LG: 1024px
- XL: 1280px

## Espaciado

- XS: 0.25rem
- SM: 0.5rem
- MD: 1rem
- LG: 1.5rem
- XL: 2rem
- 2XL: 3rem
- 3XL: 4rem

## Bordes

- Radius: 0.5rem (`rounded-lg`)
- Radio XL: 0.75rem (`rounded-xl`)
- Width: 1px (borders)

## Transiciones

- Default: `transition` (150ms)
- Duration: 150ms base
- Easing: ease-out (default)

## Ejemplos de Patrones

### Header de Vista
```html
<div>
    <p class="header-subtitle">Sección</p>
    <h1 class="header-title">Título principal</h1>
</div>
```

### Card de métrica
```html
<div class="card-base">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-muted-sm">Etiqueta</p>
            <p class="text-2xl font-semibold text-zinc-100">Valor</p>
        </div>
        <flux:icon icon="..." class="size-6 text-amber-400" />
    </div>
</div>
```

### Sección con items
```html
<div class="rounded-xl border border-zinc-700 bg-zinc-900 overflow-hidden">
    <div class="border-b border-zinc-700 px-6 py-3.5 bg-zinc-900/50">
        <h3 class="text-sm font-semibold text-zinc-100">Título</h3>
    </div>
    <div class="space-y-2 p-4">
        <!-- Items -->
    </div>
</div>
```

## Tiempo Real (Reverb)

Todas las vistas que use datos en tiempo real deben:
1. Usar componentes Livewire con listeners `#[On]`
2. Conectarse a canales privados: `kitchen`, `bar`, `waiters`
3. Canal público para cliente: `mesa.{qr_token}`
4. Validar autorización en `routes/channels.php`

## Accesibilidad

- Contrast ratio mínimo: 4.5:1 (WCAG AA)
- Todos los colores principales cumplen con esto
- Usar `aria-labels` en elementos interactivos
- Keyboard navigation: Tab, Enter, Escape
