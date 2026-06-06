# Piso Cuatro — Sistema de pedidos y gestión

Sistema completo para restaurante: menú público, pedidos por QR en tiempo real,
paneles de cocina/bar/meseros, pagos (efectivo/tarjeta/transferencia por WhatsApp)
y administración con estadísticas.

## Stack

- **Laravel 13** · PHP 8.4 · **MySQL**
- **Livewire 4 + Flux** (UI reactiva) · Fortify (auth + 2FA + passkeys)
- **Laravel Reverb** (WebSockets, tiempo real) · Tailwind 4 · Vite

## Puesta en marcha

```bash
# 1. Dependencias
composer install
npm install

# 2. Entorno
cp .env.example .env
php artisan key:generate
# Edita .env: credenciales MySQL + REVERB_APP_ID/KEY/SECRET (php artisan reverb:install los genera)

# 3. Base de datos + datos demo
php artisan migrate
php artisan db:seed

# 4. Compilar assets
npm run build

# 5. Levantar todo (servidor + cola + Reverb + Vite)
composer dev
```

`composer dev` arranca en paralelo: `php artisan serve`, `queue:listen`, **`reverb:start`** y `npm run dev`.
La cola y Reverb son necesarios para el tiempo real (los eventos se emiten con `ShouldBroadcast`, en cola).

## Accesos demo (contraseña: `password`)

| Rol | Email | Panel |
|---|---|---|
| Administrador | `admin@piso4.test` | `/admin` |
| Mesero | `mesero@piso4.test` | `/mesero` |
| Cocina | `cocina@piso4.test` | `/cocina` |
| Bar | `bar@piso4.test` | `/bar` |

- **Menú público:** `/`
- **Flujo del cliente (QR):** `/mesa/{qr_token}` — el token se ve/descarga desde Admin → Mesas y QR.

## Arquitectura (resumen)

- **Dominio** en `app/Services/*` (SessionService, OrderService, CartService, BillService,
  PaymentService, WaiterService, StatsService, QrService) + `WhatsAppGateway` desacoplado.
- **Estados** tipados en `app/Enums/*` (string en BD + cast a enum).
- **Tiempo real:** eventos en `app/Events/*` → canales privados `kitchen`/`bar`/`waiters`
  (auth por rol en `routes/channels.php`) y canal público `mesa.{qr_token}` para el cliente.
- **Pagos:** 3 modalidades (única / automática / personalizada). La transferencia genera un
  deep link `wa.me` con datos bancarios; **no se suben ni almacenan comprobantes** — el personal
  confirma manualmente. Configurable en Admin → Configuración.

## Seguridad

- RBAC nativo (un rol por usuario) + `Gate::before` (admin total) + middleware `role:`/`permission:`.
- **Componentes Livewire re-autorizan en cada petición** (guards `boot()`), no solo en la carga.
- Cliente sin login: identidad por cookie `participant_token` (httpOnly, efímera, por mesa).
- Rate limiting en rutas públicas (`mesa-public` 60/min, `mesa-join` 15/min) y en "Llamar mesero".
- Snapshots de precio/nombre en `order_items` → la cuenta histórica no cambia si el menú cambia.

## Producción

- `APP_DEBUG=false`, `APP_ENV=production`, `php artisan optimize`.
- Reverb tras TLS: `REVERB_SCHEME=https`, host real, y un worker de cola permanente
  (`php artisan queue:work`) + `php artisan reverb:start` gestionados por supervisor.

## Tests

```bash
php artisan test          # 100+ pruebas
./vendor/bin/pint         # estilo
```
