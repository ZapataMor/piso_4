# Guía de Estilos Implementados — Piso Cuatro

## 📋 Resumen de cambios

Se ha implementado un **sistema de diseño unificado** basado en una paleta de colores oscura (zinc) con acentos dorados (amber), con Livewire 4 + Flux para reactividad y Reverb para actualizaciones en tiempo real.

### Vistas actualizadas:

#### 🔓 Autenticación
- ✅ **Login** — Tema oscuro consistente con logo y marca visual
- ✅ **Register** — Formularios oscuros con validación
- ✅ **Recuperación de contraseña** — Mismo tema

#### 🍽️ Cliente (Menú QR)
- ✅ **Menú** — Interfaz oscura mejorada, categorías destacadas
- ✅ **Mis pedidos** — Estado visual de pedidos con badges
- ✅ **Cuenta** — Flujo de pago con modalidades seleccionables
- ✅ **Modales y drawers** — Animaciones y transiciones suaves

#### 👨‍💼 Personal
- ✅ **Dashboard Mesero** — Panel reactivo con llamados, cuentas, mesas
- ✅ **Panel Cocina** — Tablero Kanban de 3 columnas (pendiente→en prep→listo)
- ✅ **Panel Bar** — Mismo tablero que cocina para bebidas
- ✅ **Admin Dashboard** — Métricas, accesos rápidos, estadísticas

## 🎨 Paleta de Colores

### Fondos
```
zinc-950 (#0a0a0a) — Fondo general principal
zinc-900 (#171717) — Cards, inputs, contenedores
zinc-800 (#262626) — Borders, separadores
zinc-700 (#404040) — Borders hover
```

### Accent (Acciones)
```
amber-500 (#f59e0b) — Botones primarios, acciones principales
amber-400 (#fbbf24) — Texto destacado, badges
amber-600 (#d97706) — Hover de botones
```

### Texto
```
zinc-100 (#f5f5f5) — Títulos, texto principal
zinc-200 (#e5e5e5) — Etiquetas, subtítulos
zinc-300 (#d4d4d4) — Texto secundario
zinc-400 (#a3a3a3) — Texto terciario, placeholders
zinc-500 (#737373) — Meta información
```

### Estados
```
green-600 (#16a34a)  — Éxito, listo, confirmado
amber-600 (#d97706)  — Advertencia, pendiente
red-600 (#dc2626)    — Error, cancelado, urgente
blue-600 (#2563eb)   — Información
```

## 🧩 Componentes Disponibles

### Clases Utility CSS (en `resources/css/app.css`)

```html
<!-- Botones -->
<button class="btn-primary">Acción principal</button>
<button class="btn-secondary">Acción secundaria</button>
<button class="btn-ghost">Acción mínima</button>

<!-- Inputs -->
<input class="input-base" type="text">
<textarea class="input-base"></textarea>

<!-- Cards -->
<div class="card-base">Contenido</div>

<!-- Headers -->
<div>
    <p class="header-subtitle">Sección</p>
    <h1 class="header-title">Título</h1>
</div>

<!-- Texto -->
<p class="text-muted">Texto muted</p>
<p class="text-muted-sm">Texto pequeño muted</p>
```

### Componentes Blade Reutilizables

#### Dashboard — Métrica Card
```html
<x-dashboard.metric-card 
    label="Etiqueta"
    value="100"
    icon="currency-dollar"
    variant="success" />
```

Variantes: `default`, `success`, `warning`, `error`, `info`

#### Dashboard — Sección
```html
<x-dashboard.section 
    title="Título"
    icon="chart-bar"
    count="5"
    variant="warning">
    <!-- Contenido dentro -->
</x-dashboard.section>
```

#### Botón Primario Reutilizable
```html
<x-btn.primary size="lg" :disabled="false" :loading="false">
    Texto del botón
</x-btn.primary>
```

Tamaños: `sm`, `md`, `lg`

#### Input de Formulario
```html
<x-form.input 
    name="email"
    label="Email"
    type="email"
    placeholder="email@example.com"
    :error="$errors->first('email')"
    hint="Usa tu email de trabajo"
/>
```

## 📱 Layouts

### Layout del Cliente (Mesa QR)
```blade
<!-- Usa layouts.customer -->
@extends('layouts.customer')

<!-- Características -->
- Ancho máximo: 28rem (lg)
- Header sticky
- Main con padding bottom para fixed bar
- Toast al final
```

### Layouts de Personal
```blade
<!-- Usa layouts.app (con sidebar) -->
@extends('layouts.app')

<!-- Características -->
- Sidebar collapsible
- Main area amplia
- Responsive grid
```

## 🌐 Tiempo Real (Reverb)

Todas las vistas reactivas usan **Laravel Reverb** para WebSockets:

### Canales Privados
- `kitchen` — Eventos de cocina
- `bar` — Eventos de bar
- `waiters` — Eventos de meseros

### Canal Público
- `mesa.{qr_token}` — Eventos de cliente en mesa específica

### Listeners en Componentes Livewire

```php
#[On('echo-private:kitchen,.order.placed')]
#[On('echo-private:kitchen,.order.item.status')]
public function onRealtime(): void
{
    $this->forget(); // Refresca datos
}
```

## 🔧 Cómo ejecutar localmente

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Base de datos
php artisan migrate
php artisan db:seed

# 4. Compilar assets
npm run build

# 5. Ejecutar todo en paralelo
composer dev
```

Esto arranca:
- PHP Laravel server (puerto 8000)
- Vite dev server (hot reload)
- Queue listener (para cola de trabajos)
- Reverb WebSocket server (puerto 8080)

## 📖 Guía de uso por vista

### 🔐 Login (`/login`)
- Formulario oscuro
- Logo en encabezado
- Links de recuperación de contraseña
- Soporta Passkeys (Fortify)

### 🍕 Menú de Cliente (`/mesa/{qr_token}/menu`)
- Categorías con subtítulos
- Productos con descripción y precio
- Modal para agregar (cantidad + notas)
- Carrito flotante (drawer)
- Actualización en tiempo real de pedidos

### 📋 Mis Pedidos (`/mesa/{qr_token}/pedidos`)
- Lista de pedidos con estados
- Color-coded badges (rojo=cancelado, ámbar=pendiente, verde=listo)
- Enlaces rápidos a menú y cuenta

### 💳 Cuenta (`/mesa/{qr_token}/cuenta`)
- 3 pasos: solicitar → elegir modalidad → pagar
- Soporta pago único, automático, personalizado
- Integración con WhatsApp para transferencias
- Confirmación de pagos en tiempo real

### 👨‍💼 Panel Mesero (`/mesero`)
- **Llamados**: Mesa roja, botón rápido para atender
- **Cuentas**: Modalidad y método de pago seleccionable
- **Listos**: Productos para servir
- **Mesas activas**: Estado, personas, pedidos, total
- Actualización en vivo con Reverb

### 🍳 Cocina (`/cocina`)
- 3 columnas: Pendientes | En prep | Listos
- Botones de cambio de estado
- Notas especiales destacadas
- Alto contraste para ambiente de cocina

### 🍹 Bar (`/bar`)
- Mismo layout que cocina
- Optimizado para bebidas

### ⚙️ Admin Dashboard (`/admin`)
- Métricas en tiempo real: ventas, mesas, ítems, pedidos
- Accesos rápidos a gestión
- Actualización con botón manual

## 🎯 Mejores prácticas

### Nuevas vistas
1. **Usa las clases utility** cuando sea posible
2. **Reutiliza componentes** (metric-card, section, btn-primary)
3. **Asegúrate de dark mode** en Tailwind
4. **Respeta la paleta** de colores

### Formularios
- Usa `<x-form.input>` para inputs consistentes
- Usa `<x-btn.primary>` para botones
- Valida en el servidor, muestra errores al usuario

### Tiempo real
- Todos los cambios de estado → evento Reverb
- Listeners en componentes que muestren datos dinámicos
- Test con múltiples pestañas abiertas

## 📄 Archivos de referencia

- `DESIGN_SYSTEM.md` — Sistema completo con detalles
- `resources/css/app.css` — Clases utility personalizadas
- `resources/views/components/` — Componentes Blade reutilizables
- `resources/views/pages/` — Vistas específicas por rol

## 🚀 Próximos pasos

1. **Ajustes de performance** — Lazy load de imágenes, caché
2. **Pruebas E2E** — Validar flujos completos en navegador
3. **Optimización de Reverb** — Monitoreo de conexiones
4. **Temas adicionales** — Soporte para light mode (futuro)
5. **Internacionalización** — Textos multiidioma

## ⚠️ Consideraciones importantes

- **JavaScript obligatorio** — Flux y Livewire requieren JS habilitado
- **WebSocket** — Reverb requiere conexión abierta (reverb:start ejecutándose)
- **Mobile first** — Diseño responsive desde mobile hasta desktop
- **Seguridad** — RBAC en todas las rutas, re-autorización en cada request Livewire
