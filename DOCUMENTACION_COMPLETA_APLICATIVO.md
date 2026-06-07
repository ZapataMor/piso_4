# Documentacion completa del aplicativo Piso Cuatro

Actualizado: 2026-06-07

Este documento describe el aplicativo completo de Piso Cuatro para que una IA,
un desarrollador o una persona nueva en el proyecto entienda sus tecnologias,
vistas, rutas, roles, reglas de negocio y flujos operativos.

## 1. Resumen general

Piso Cuatro es un sistema Laravel para restaurante y bar. Cubre dos mundos
principales:

1. Una experiencia publica de marca en `/`, con landing visual, animacion de
   ascensor, carta informativa y enlaces de reserva por WhatsApp.
2. Un sistema operativo por mesa mediante QR, donde el cliente entra sin crear
   cuenta, pide productos, ve el estado de sus pedidos, solicita la cuenta,
   divide pagos y se comunica con el personal.

Ademas incluye paneles internos autenticados para administrador, meseros,
cocina y bar. El sistema maneja roles, permisos, productos, categorias, mesas,
QR, sesiones de mesa, participantes, carritos, pedidos, items de pedido,
estados de preparacion, llamados al mesero, cuentas, pagos, configuracion,
estadisticas y actualizaciones en tiempo real con Laravel Reverb.

## 2. Tecnologias utilizadas

Backend:

- PHP `^8.3`; el Dockerfile usa imagen base `php:8.4-apache`.
- Laravel `13.7`.
- Laravel Fortify para autenticacion, registro, recuperacion de clave,
  verificacion de email, doble factor y passkeys.
- Laravel Passkeys para WebAuthn/passkeys.
- Laravel Reverb para WebSockets en tiempo real.
- Livewire `4.1` para componentes reactivos.
- Flux `2.13` para UI Blade/Livewire.
- MySQL en produccion/desarrollo normal, segun `.env`.
- BaconQrCode para generar QR en SVG.

Frontend:

- Blade.
- Livewire single-file components en `resources/views/pages/**`.
- Tailwind CSS `4`.
- Vite `8`.
- Laravel Echo y Pusher JS como cliente del protocolo Reverb/Pusher.
- Alpine se usa en interacciones de UI dentro de Blade.
- CSS propio en `resources/css/app.css`.
- Assets publicos del menu en `public/piso-cuatro-menu`.

Infraestructura:

- `composer dev` levanta servidor Laravel, cola, Reverb y Vite en paralelo.
- `npm run build` compila assets.
- Dockerfile listo para Apache/PHP, Node 22, Composer y build de assets.
- Health check de Laravel en `/up`.

## 3. Estructura principal del proyecto

- `routes/web.php`: entrada publica, dashboard autenticado, flujo QR del cliente
  e inclusion de rutas admin, staff y settings.
- `routes/admin.php`: panel administrativo protegido por rol `admin`.
- `routes/staff.php`: paneles de cocina, bar y meseros.
- `routes/settings.php`: perfil, apariencia y seguridad del usuario.
- `routes/channels.php`: canales privados de broadcast para staff.
- `app/Models`: modelos del dominio.
- `app/Enums`: estados y tipos del negocio.
- `app/Services`: reglas centrales del restaurante.
- `app/Events`: eventos broadcast para tiempo real.
- `app/Http/Middleware`: control de roles, permisos y participante QR.
- `app/Http/Controllers`: entrada de mesa, dashboard y CRUD de mesas.
- `resources/views/pages`: vistas Livewire autenticadas y de cliente.
- `resources/views/customer`: entrada publica por mesa y estado no disponible.
- `resources/views/layouts`: layouts de app, auth y cliente.
- `resources/css/app.css`: sistema visual de Piso Cuatro.
- `resources/js/echo.js`: configuracion del cliente Reverb/Echo.
- `public/piso-cuatro-menu`: landing/carta publica con assets, scripts y estilos.
- `database/migrations`: esquema de usuarios, roles, menu, mesas, sesiones,
  pedidos, pagos, llamados y configuracion.
- `database/seeders`: roles, permisos, usuarios demo, mesas, menu y settings.
- `tests/Feature`: pruebas de autenticacion, cliente, staff, admin y estadisticas.

## 4. Roles del sistema

Los roles estan definidos por `App\Enums\RoleType` y se guardan en `roles.slug`.

- `admin`: administrador. Tiene acceso total por `Gate::before`.
- `mesero`: opera llamados, entregas, pagos y cierre de mesas.
- `cocina`: ve y mueve items de productos cuya estacion sea cocina.
- `bar`: ve y mueve items de productos cuya estacion sea bar.

Cada usuario tiene un solo `role_id`, puede estar activo/inactivo y puede tener
telefono. Si un usuario no esta activo, los middlewares y componentes operativos
bloquean el acceso.

Permisos sembrados:

- `orders.view`
- `kitchen.operate`
- `bar.operate`
- `waiter.operate`
- `mesas.view`
- `mesas.manage`
- `sessions.manage`
- `menu.view`
- `menu.manage`
- `payments.view`
- `payments.manage`
- `users.manage`
- `roles.manage`
- `settings.manage`
- `stats.view`

El administrador recibe todos. Mesero, cocina y bar reciben solo los permisos
necesarios para operar su parte.

## 5. Autenticacion y seguridad

La autenticacion interna usa Fortify. Las rutas principales son:

- `GET /login` y `POST /login`.
- `POST /logout`.
- `GET /register` y `POST /register`.
- `GET /forgot-password` y `POST /forgot-password`.
- `GET /reset-password/{token}` y `POST /reset-password`.
- `GET /email/verify` y rutas de verificacion.
- `GET/POST /two-factor-challenge`.
- Rutas de confirmacion de password.
- Rutas de passkeys en `/passkeys/*` y `/user/passkeys/*`.
- Rutas de doble factor en `/user/two-factor-*`.

Vistas de auth:

- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/auth/forgot-password.blade.php`
- `resources/views/pages/auth/reset-password.blade.php`
- `resources/views/pages/auth/verify-email.blade.php`
- `resources/views/pages/auth/two-factor-challenge.blade.php`
- `resources/views/pages/auth/confirm-password.blade.php`

Despues de iniciar sesion, Fortify redirige a `/dashboard`. El controlador
`DashboardController` revisa el rol del usuario y lo manda a:

- Admin: `admin.dashboard` (`/admin`)
- Mesero: `waiter.dashboard` (`/mesero`)
- Cocina: `kitchen.board` (`/cocina`)
- Bar: `bar.board` (`/bar`)

Seguridad adicional:

- `role:` protege rutas por rol.
- `permission:` protege rutas por permiso.
- El administrador tiene bypass total.
- Las rutas publicas de mesa tienen rate limit:
  - `mesa-public`: 60 peticiones por minuto por IP.
  - `mesa-join`: 15 peticiones por minuto por IP.
- Login, 2FA y passkeys tambien tienen limitadores Fortify.
- En produccion se fuerza HTTPS.
- En produccion Laravel prohibe comandos destructivos de base de datos.
- Passwords en produccion usan reglas fuertes.

## 6. Menu publico informativo

Ruta: `/`

Vista: `resources/views/welcome.blade.php`

Esta pantalla no requiere login y no crea pedidos. Es una experiencia publica
de marca:

- Animacion inicial de ascensor con boton `Subir`.
- Audio de ascensor.
- Fondo con humo/burbujas en video.
- Hero con logo, slogan y CTA.
- Navegacion a secciones de carta.
- Render de capitulos de menu desde `public/piso-cuatro-menu/menu-data.js`.
- Enlaces de reserva por WhatsApp.
- Informacion de direccion, horario, telefono e Instagram.

Assets:

- `public/piso-cuatro-menu/styles.css`
- `public/piso-cuatro-menu/intro.css`
- `public/piso-cuatro-menu/app.js`
- `public/piso-cuatro-menu/intro.js`
- `public/piso-cuatro-menu/assets/*`
- `public/piso-cuatro-menu/menu_pages/*`

Importante: este menu publico es informativo/promocional. El flujo operativo
para pedir desde la mesa ocurre en `/mesa/{qr_token}`.

## 7. Flujo completo del cliente por QR

Rutas:

- `GET /mesa/{mesa:qr_token}`: pantalla de entrada por mesa.
- `POST /mesa/{mesa:qr_token}/entrar`: registra participante.
- `GET /mesa/{mesa:qr_token}/menu`: menu operativo.
- `GET /mesa/{mesa:qr_token}/pedidos`: seguimiento de pedidos.
- `GET /mesa/{mesa:qr_token}/cuenta`: solicitud y pago de cuenta.

El cliente no crea cuenta de usuario. Se identifica con una cookie
`participant_token` que apunta a un `SessionParticipant` dentro de la sesion
activa de la mesa.

### 7.1 Entrada por QR

Controlador: `TableEntryController`

Vista: `resources/views/customer/entry.blade.php`

Al escanear el QR:

1. Laravel resuelve la mesa por `qr_token`.
2. Si la mesa esta `fuera_de_servicio`, responde la vista
   `customer.unavailable` con estado HTTP 503.
3. Si la mesa esta disponible u ocupada, `SessionService` abre o reutiliza una
   sesion activa.
4. Si el dispositivo ya tiene cookie valida para esa sesion, redirige al menu.
5. Si no, muestra un formulario para ingresar nombre.
6. Al enviar el nombre, se crea un `SessionParticipant`.
7. Se guarda cookie `participant_token` por 8 horas.
8. El cliente entra a `/menu`.

Reglas:

- El nombre es obligatorio, string, minimo 2 y maximo 40 caracteres.
- La primera persona de la sesion queda marcada como `is_host`.
- Una mesa solo puede tener una sesion activa.
- La apertura de sesion bloquea fila con `lockForUpdate` para evitar sesiones
  duplicadas si dos personas escanean al tiempo.

### 7.2 Menu operativo del cliente

Vista Livewire: `resources/views/pages/customer/⚡menu.blade.php`

Layout: `layouts.customer`

Funciones:

- Muestra categorias activas y ordenadas.
- Solo muestra categorias que tengan productos disponibles.
- Permite abrir/cerrar categorias.
- Muestra productos con nombre, descripcion, nota, precio y destacado.
- Al tocar un producto abre modal.
- En el modal se elige cantidad de 1 a 50.
- Permite escribir notas especiales.
- Agrega al carrito.
- Une lineas iguales si tienen mismo producto y mismas notas.
- Permite abrir drawer de carrito.
- Permite aumentar/disminuir cantidad.
- Si cantidad queda en 0, elimina item del carrito.
- Permite eliminar item.
- Calcula subtotal del carrito.
- Envia pedido.
- Permite llamar al mesero.
- Permite ir a pedidos.

Servicios usados:

- `CartService`: add, setQuantity, remove, clear, total.
- `OrderService`: convierte carrito en pedido real.
- `WaiterService` mediante trait `ResolvesParticipant` para llamar mesero.

Al pulsar `Enviar Pedido`:

1. Lee carrito del participante.
2. Crea un `Order`.
3. Genera numero consecutivo dentro de la sesion.
4. Crea `OrderItem` por cada linea del carrito.
5. Guarda snapshot de producto: nombre, precio, estacion y notas.
6. Calcula subtotal.
7. Borra carrito.
8. Dispara evento `OrderPlaced`.
9. Redirige a `mesa.orders`.

### 7.3 Pedidos de la mesa

Vista Livewire: `resources/views/pages/customer/⚡orders.blade.php`

Funciones:

- Lista todos los pedidos de la sesion activa.
- Agrupa por participante.
- Muestra numero de pedido, estado del pedido y subtotal.
- Muestra cada item con cantidad, nombre, notas y estado.
- Calcula total de mesa.
- Botones para:
  - llamar mesero,
  - ir a cuenta,
  - volver al menu.
- Escucha canal publico `mesa.{qr_token}` para refrescar cuando cambia un item.

El cliente ve estados como pendiente, en preparacion, listo y entregado segun
las transiciones hechas por cocina/bar/mesero.

### 7.4 Cuenta del cliente

Vista Livewire: `resources/views/pages/customer/⚡bill.blade.php`

Funciones:

- Muestra cuenta global de la mesa.
- Agrupa consumos por participante.
- Suma items no cancelados.
- Solicita la cuenta.
- Permite elegir modalidad de pago.
- Permite previsualizar pagos antes de generarlos.
- Genera pagos pendientes.
- Permite seleccionar metodo: efectivo, transferencia o tarjeta.
- Para transferencia, pide nombre y telefono de quien transfiere.
- Genera link de WhatsApp con mensaje prellenado.
- Permite recalcular/cambiar forma de pago si aun hay pagos pendientes.
- Escucha `payment.confirmed` por canal publico de la mesa.

Estados de pantalla:

- `request`: aun no existe cuenta; muestra boton Solicitar cuenta.
- `split`: existe cuenta o se esta editando; el cliente elige modalidad.
- `pay`: ya existen pagos generados; el cliente selecciona metodo y ve estado.

Modalidades:

- Cuenta unica: una persona paga todo.
- Division automatica: cada participante paga lo que pidio.
- Division personalizada: se asigna cada item a un participante.

Al pulsar `Solicitar cuenta`:

1. `BillService::requestBill` crea o reutiliza la cuenta de la sesion.
2. Calcula total con items no cancelados.
3. Si la cuenta se acaba de crear, dispara `BillRequested`.
4. Tambien crea un llamado de mesero tipo `cuenta`.

Al pulsar `Generar pagos`:

1. `PaymentService::generate` elimina pagos pendientes anteriores.
2. Conserva pagos ya confirmados.
3. Actualiza modalidad y total.
4. Crea pagos pendientes.
5. Si la modalidad es personalizada, vincula items al pago en el pivote
   `payment_order_item`.

Al seleccionar transferencia:

1. Se guardan nombre y telefono del pagador.
2. `DeepLinkWhatsApp` arma un mensaje con mesa, sesion, valor y datos bancarios.
3. El cliente abre WhatsApp con `wa.me`.
4. El sistema no sube ni almacena comprobantes.
5. El personal confirma manualmente el pago.

## 8. Panel del mesero

Ruta: `/mesero`

Vista Livewire: `resources/views/pages/waiter/⚡dashboard.blade.php`

Acceso:

- Rol `mesero` o `admin`.
- Reautoriza en cada peticion Livewire.

Funciones:

- Ver llamados pendientes de todas las mesas.
- Atender llamado.
- Ver productos listos para entregar.
- Marcar item como entregado.
- Ver cuentas solicitadas o en pago.
- Confirmar pagos pendientes.
- Ver mesas con sesion activa.
- Cerrar mesa activa.
- Refrescar tablero manualmente.
- Refrescar automaticamente por Reverb.

Secciones:

- Llamados urgentes.
- Cuentas pendientes.
- Listos para servir.
- Mesas activas.

Acciones:

- `attend(callId)`: marca llamado como atendido, guarda usuario y hora.
- `deliver(itemId)`: item `listo` pasa a `entregado`.
- `confirmPayment(paymentId)`: pago pasa a confirmado.
- `closeSession(sessionId)`: sesion pasa a cerrada y mesa a disponible.
- `refreshBoard()`: limpia caches computed.

## 9. Paneles de cocina y bar

Rutas:

- `/cocina`
- `/bar`

Vistas:

- `resources/views/pages/kitchen/⚡board.blade.php`
- `resources/views/pages/bar/⚡board.blade.php`
- `resources/views/partials/station-board.blade.php`

Ambas usan el trait `HasStationBoard`.

Acceso:

- Cocina: rol `cocina` o `admin`.
- Bar: rol `bar` o `admin`.
- La autorizacion ocurre en cada peticion Livewire.

Funcionamiento:

- Cada producto tiene `tipo_preparacion`: `cocina` o `bar`.
- Cuando se crea un pedido, cada item se enruta a su estacion.
- El tablero consulta items de su estacion en estados:
  - pendiente,
  - en_preparacion,
  - listo.
- Muestra tres columnas: Pendientes, En preparacion y Listos.
- Cada tarjeta muestra mesa, hora, producto, cantidad, participante y notas.

Acciones:

- `start(itemId)`: pendiente -> en_preparacion.
- `ready(itemId)`: en_preparacion -> listo.
- `refreshBoard()`: refresca datos.

Regla de seguridad:

Aunque alguien manipule un ID, el trait busca el item filtrado por
`tipo_preparacion` de la estacion actual. Bar no puede mover lineas de cocina
y cocina no puede mover lineas de bar.

## 10. Panel administrativo

Ruta base: `/admin`

Acceso:

- Requiere `auth`, `verified` y rol `admin`.

### 10.1 Dashboard admin

Ruta: `/admin`

Vista: `resources/views/pages/admin/⚡dashboard.blade.php`

Muestra metricas en vivo:

- Ventas de hoy.
- Mesas activas.
- Items pendientes/en preparacion.
- Pedidos de hoy.

Tambien incluye accesos rapidos a:

- Productos.
- Categorias.
- Mesas y QR.
- Usuarios.
- Pedidos.
- Estadisticas.
- Configuracion.

Escucha eventos de sesiones, pedidos e items para refrescar metricas.

### 10.2 Productos

Ruta: `/admin/productos`

Vista: `resources/views/pages/admin/⚡products.blade.php`

Funciones:

- Listar productos.
- Buscar por nombre o categoria.
- Filtrar por categoria.
- Ordenar por nombre, categoria o precio.
- Crear producto.
- Editar producto.
- Eliminar producto.
- Activar/desactivar disponibilidad.
- Marcar producto como destacado.
- Asignar categoria.
- Definir precio.
- Definir estacion: cocina o bar.
- Definir subgrupo visual dentro de la categoria.
- Ver totales de productos y disponibles.

Campos:

- Nombre.
- Categoria.
- Descripcion.
- Precio COP.
- Estacion.
- Subgrupo.
- Disponible.
- Destacado.

Reglas:

- El producto pertenece a una categoria.
- La categoria no se puede eliminar si tiene productos.
- Los productos tienen soft delete.
- Los items de pedido guardan snapshots para conservar historico.

### 10.3 Categorias

Ruta: `/admin/categorias`

Vista: `resources/views/pages/admin/⚡categories.blade.php`

Funciones:

- Listar categorias.
- Buscar por nombre, slug, kicker o subtitulo.
- Crear categoria.
- Editar categoria.
- Eliminar categoria si no tiene productos.
- Activar/desactivar categoria.
- Definir orden del menu.
- Definir slug.
- Definir kicker y subtitulo.
- Ver cantidad de productos por categoria.

Campos:

- Nombre.
- Slug.
- Kicker.
- Subtitulo.
- Orden.
- Activa.

### 10.4 Mesas y QR

Rutas:

- `GET /admin/mesas`
- `GET /admin/mesas/create`
- `POST /admin/mesas`
- `GET /admin/mesas/{mesa}/edit`
- `PUT/PATCH /admin/mesas/{mesa}`
- `DELETE /admin/mesas/{mesa}`
- `GET /admin/mesas/{mesa}/qr`
- `POST /admin/mesas/{mesa}/qr/regenerate`

Controlador: `Admin\MesaController`

Vistas:

- `resources/views/admin/mesas/index.blade.php`
- `resources/views/admin/mesas/create.blade.php`
- `resources/views/admin/mesas/edit.blade.php`
- `resources/views/admin/mesas/_form.blade.php`

Funciones:

- Crear mesa.
- Editar numero, nombre, capacidad y estado.
- Eliminar mesa con soft delete.
- Descargar QR en SVG.
- Ver QR inline en la vista de edicion.
- Regenerar QR.

Estados:

- `disponible`
- `ocupada`
- `fuera_de_servicio`

Reglas:

- `numero` es unico.
- `qr_token` es unico y aleatorio.
- El QR codifica la URL publica `/mesa/{qr_token}`.
- Regenerar QR invalida el codigo anterior.
- Si una mesa esta fuera de servicio, el cliente recibe pantalla de no
  disponible y no puede entrar.

### 10.5 Usuarios

Ruta: `/admin/usuarios`

Vista: `resources/views/pages/admin/⚡users.blade.php`

Funciones:

- Listar usuarios.
- Buscar por nombre, email, telefono o rol.
- Filtrar por rol.
- Crear usuario.
- Editar usuario.
- Cambiar password.
- Asignar rol.
- Guardar telefono.
- Activar/desactivar cuenta.
- Eliminar usuario.

Reglas:

- No se puede desactivar la propia cuenta.
- No se puede eliminar la propia cuenta.
- Password se hashea por cast del modelo.
- Usuarios creados desde admin quedan con email verificado.

### 10.6 Pedidos admin

Ruta: `/admin/pedidos`

Vista: `resources/views/pages/admin/⚡orders.blade.php`

Funciones:

- Ver pedidos recientes.
- Filtrar por estado.
- Agrupar pedidos por mesa.
- Ver clientes de la mesa.
- Ver cantidad de pedidos e items.
- Ver total por mesa.
- Ver hora del ultimo pedido.
- Ver cada pedido con subtotal, fecha, estado e items.
- Refrescar por eventos Reverb.

Estados filtrables:

- Todos.
- Pendiente.
- En preparacion.
- Listo.
- Entregado.
- Facturado.
- Cancelado.

### 10.7 Estadisticas

Ruta: `/admin/estadisticas`

Vista: `resources/views/pages/admin/⚡statistics.blade.php`

Servicio: `StatsService`

Muestra:

- Ventas de hoy.
- Ventas de la semana.
- Ventas del mes.
- Tiempo promedio de preparacion.
- Ventas de los ultimos 7 dias.
- Pedidos por hora.
- Productos mas vendidos.
- Mesas mas utilizadas.
- Ingresos por metodo de pago.

Regla importante:

Ventas significa pagos confirmados, no pedidos creados. Se calcula desde
`payments.confirmed_at` y `payments.monto`.

### 10.8 Configuracion

Ruta: `/admin/configuracion`

Vista: `resources/views/pages/admin/⚡settings.blade.php`

Funciones:

- Editar nombre del restaurante.
- Editar numero de WhatsApp del restaurante.
- Editar banco.
- Editar tipo de cuenta.
- Editar numero de cuenta.
- Editar titular.
- Editar NIT/CC.

Estos datos alimentan el mensaje de transferencia por WhatsApp.

## 11. Settings del usuario autenticado

Rutas:

- `/settings/profile`
- `/settings/security`
- `/settings/appearance`

Vistas:

- `resources/views/pages/settings/⚡profile.blade.php`
- `resources/views/pages/settings/⚡security.blade.php`
- `resources/views/pages/settings/⚡appearance.blade.php`
- Componentes auxiliares de 2FA, recovery codes y eliminar usuario.

Funciones:

- Editar nombre y email.
- Reenviar verificacion de email.
- Cambiar password.
- Activar/desactivar doble factor.
- Confirmar doble factor cuando aplica.
- Ver/regenerar codigos de recuperacion.
- Administrar passkeys.
- Eliminar cuenta propia.
- Elegir apariencia.

La ruta de seguridad requiere confirmacion de password.

## 12. Servicios de dominio

### 12.1 SessionService

Gestiona sesiones de mesa.

- `openOrGetActiveSession(Mesa $mesa)`: abre o reutiliza sesion activa.
- `addParticipant(RestaurantSession $session, string $nombre)`: agrega cliente.
- `closeSession(RestaurantSession $session)`: cierra sesion y libera mesa.

Reglas:

- Solo una sesion activa por mesa.
- Usa transacciones y `lockForUpdate`.
- Al abrir sesion marca mesa como ocupada.
- Al cerrar sesion marca mesa como disponible.
- Dispara `SessionStatusChanged` al abrir o cerrar.

### 12.2 CartService

Gestiona carrito borrador por participante.

- `add`: agrega producto y une lineas iguales.
- `setQuantity`: cambia cantidad o elimina si queda en 0.
- `remove`: elimina item.
- `clear`: limpia carrito.
- `total`: calcula total del carrito.

### 12.3 OrderService

Convierte carrito en pedido y gestiona estados.

- `submitOrder`: carrito -> order + order_items.
- `nextOrderNumber`: consecutivo por sesion.
- `startItem`: pendiente -> en_preparacion.
- `markItemReady`: en_preparacion -> listo.
- `deliverItem`: listo -> entregado.
- `cancelItem`: cancela linea.
- `syncOrderStatus`: recalcula estado agregado del pedido.

Estados agregados del pedido:

- Si todos los items activos estan entregados: `entregado`.
- Si todos estan listos o entregados: `listo`.
- Si alguno ya empezo, esta listo o entregado: `en_preparacion`.
- Si todos estan cancelados: `cancelado`.
- De lo contrario: `pendiente`.

### 12.4 BillService

Gestiona solicitud y calculo de cuenta.

- `requestBill`: crea/reutiliza una cuenta por sesion.
- `computeTotal`: suma items no cancelados.

Reglas:

- Una sola cuenta por sesion.
- La solicitud es idempotente.
- El total no incluye propina ni IVA adicional.
- Dispara `BillRequested` solo cuando la cuenta se crea por primera vez.

### 12.5 PaymentService

Gestiona division, generacion y confirmacion de pagos.

- `shares`: calcula porciones sin persistir.
- `generate`: crea pagos pendientes segun modalidad.
- `setMethod`: actualiza efectivo, transferencia o tarjeta.
- `confirm`: confirma pago manualmente por usuario staff.
- `syncBillStatus`: si todos los pagos estan confirmados, cuenta pasa a pagada.

Reglas:

- Reemplaza pagos no confirmados al recalcular.
- No borra pagos confirmados.
- Si todos los pagos estan confirmados:
  - `bill.estado` pasa a `pagada`;
  - pedidos no cancelados de la sesion pasan a `facturado`.

### 12.6 WaiterService

Gestiona llamados de mesero.

- `call`: crea llamado pendiente.
- `attend`: marca llamado como atendido.

Tipos:

- `llamado`: llamado normal.
- `cuenta`: solicitud de cuenta.

### 12.7 MesaService y QrService

`MesaService`:

- Crea mesa con token QR.
- Actualiza mesa.
- Regenera token.
- Elimina mesa con soft delete.

`QrService`:

- Genera token unico de 40 caracteres hex.
- Genera SVG del QR.
- Genera SVG inline sin prologo XML.
- El QR apunta a `mesa.public_url`.

### 12.8 StatsService

Calcula metricas del negocio.

- Ventas entre fechas.
- Ventas hoy, semana y mes.
- Ventas por dia.
- Top productos.
- Top mesas.
- Promedio de preparacion.
- Pedidos por hora.
- Ingresos por metodo.
- Snapshot en vivo para dashboard admin.

### 12.9 WhatsAppGateway / DeepLinkWhatsApp

Contrato: `App\Contracts\WhatsAppGateway`

Implementacion: `App\Services\WhatsApp\DeepLinkWhatsApp`

Funciones:

- Arma mensaje de pago por transferencia.
- Genera link `https://wa.me/{numero}?text={mensaje}`.
- Usa settings de banco y WhatsApp.
- No sube comprobantes.
- No valida pago automaticamente.

## 13. Eventos y tiempo real

Cliente JS: `resources/js/echo.js`

Echo solo se inicializa si:

- `VITE_REVERB_ENABLED=true`
- existe `VITE_REVERB_APP_KEY`
- existe `VITE_REVERB_HOST`

Canales privados:

- `kitchen`: admin o cocina.
- `bar`: admin o bar.
- `waiters`: admin o mesero.

Canal publico:

- `mesa.{qr_token}` para clientes sin login.

Eventos:

- `OrderPlaced`
  - Nombre broadcast: `order.placed`
  - Canales: `waiters` y la estacion correspondiente (`kitchen`, `bar` o ambas).
  - Uso: refrescar tableros cuando entra pedido nuevo.

- `OrderItemStatusChanged`
  - Nombre broadcast: `order.item.status`
  - Canales: estacion, `waiters` y `mesa.{qr_token}`.
  - Uso: cocina/bar/meseros/clientes ven cambios de estado.

- `BillRequested`
  - Nombre broadcast: `bill.requested`
  - Canal: `waiters`.
  - Uso: meseros ven cuenta solicitada.

- `WaiterCalled`
  - Nombre broadcast: `waiter.called`
  - Canal: `waiters`.
  - Uso: meseros ven llamado normal o cuenta.

- `PaymentConfirmed`
  - Nombre broadcast: `payment.confirmed`
  - Canales: `waiters` y `mesa.{qr_token}`.
  - Uso: cliente ve pago confirmado y mesero refresca tablero.

- `SessionStatusChanged`
  - Nombre broadcast: `session.changed`
  - Canal: `waiters`.
  - Uso: refrescar mesas activas y metricas.

## 14. Modelo de datos principal

### Roles y usuarios

- `users`: nombre, email, password, role_id, is_active, phone, 2FA.
- `roles`: slug, name, description.
- `permissions`: slug, name, group, description.
- `permission_role`: permisos asignados a roles.
- `passkeys`: credenciales WebAuthn por usuario.

### Carta

- `categories`: slug, name, kicker, subtitle, photo, bg, display_order,
  is_active.
- `products`: category_id, name, slug, description, price, tipo_preparacion,
  group_label, image, note, is_available, is_featured, display_order.

### Mesas y sesiones

- `mesas`: numero, nombre, qr_token, estado, capacidad, soft deletes.
- `restaurant_sessions`: mesa_id, codigo, estado, fecha_inicio, fecha_fin.
- `session_participants`: restaurant_session_id, nombre, token, is_host,
  last_seen_at.

### Carrito y pedidos

- `cart_items`: participant, product, quantity, notes.
- `orders`: session, participant, mesa, numero, estado, subtotal, notes,
  placed_at, started_at, ready_at, delivered_at.
- `order_items`: order, product, product_name snapshot, unit_price snapshot,
  quantity, tipo_preparacion snapshot, estado, notes, prepared_by, timestamps.

### Cuenta y pagos

- `bills`: session unica, requested_by, modalidad, estado, total, requested_at,
  closed_at.
- `payments`: bill, participant, metodo, estado, monto, payer_nombre,
  payer_telefono, reference, confirmed_by, confirmed_at.
- `payment_order_item`: pivote para division personalizada.

### Llamados y configuracion

- `waiter_calls`: session, participant, mesa, tipo, estado, note, attended_by,
  attended_at.
- `settings`: key, value, type, group.

## 15. Estados del negocio

Mesa:

- `disponible`
- `ocupada`
- `fuera_de_servicio`

Sesion:

- `activa`
- `cerrada`

Pedido:

- `pendiente`
- `en_preparacion`
- `listo`
- `entregado`
- `facturado`
- `cancelado`

Item de pedido:

- `pendiente`
- `en_preparacion`
- `listo`
- `entregado`
- `cancelado`

Cuenta:

- `solicitada`
- `en_pago`
- `pagada`
- `cerrada`
- `cancelada`

Pago:

- `pago_pendiente`
- `pago_confirmado`
- `cancelado`

Metodo de pago:

- `efectivo`
- `transferencia`
- `tarjeta`

Modalidad de cuenta:

- `unica`
- `automatica`
- `personalizada`

Estacion de preparacion:

- `cocina`
- `bar`

Llamado al mesero:

- tipo: `llamado` o `cuenta`
- estado: `pendiente` o `atendido`

## 16. Rutas principales

Publicas:

- `/`: landing/menu publico.
- `/mesa/{qr_token}`: entrada del cliente por QR.
- `/mesa/{qr_token}/entrar`: crear participante.
- `/mesa/{qr_token}/menu`: menu operativo.
- `/mesa/{qr_token}/pedidos`: pedidos de la mesa.
- `/mesa/{qr_token}/cuenta`: cuenta y pagos.

Autenticacion:

- `/login`
- `/register`
- `/forgot-password`
- `/reset-password/{token}`
- `/email/verify`
- `/two-factor-challenge`
- `/logout`

Dashboard:

- `/dashboard`: redireccion por rol.

Admin:

- `/admin`
- `/admin/productos`
- `/admin/categorias`
- `/admin/usuarios`
- `/admin/pedidos`
- `/admin/estadisticas`
- `/admin/configuracion`
- `/admin/mesas`
- `/admin/mesas/create`
- `/admin/mesas/{mesa}/edit`
- `/admin/mesas/{mesa}/qr`
- `/admin/mesas/{mesa}/qr/regenerate`

Staff:

- `/mesero`
- `/cocina`
- `/bar`

Settings:

- `/settings/profile`
- `/settings/security`
- `/settings/appearance`

## 17. Como se ejecuta el aplicativo

Instalacion local:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

Modo desarrollo completo:

```bash
composer dev
```

Ese comando ejecuta en paralelo:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `php artisan reverb:start`
- `npm run dev`

Modo frontend:

```bash
npm run dev
npm run build
```

Tests:

```bash
php artisan test
composer test
composer lint
composer lint:check
```

Produccion con Docker:

- El Dockerfile instala PHP, Apache, extensiones, Composer y Node.
- Ejecuta `composer install --no-dev`.
- Ejecuta `npm ci`.
- Ejecuta `npm run build`.
- Expone el puerto definido por `PORT`, por defecto `10000`.
- Usa `render/apache.conf`.

## 18. Datos demo

Seeder de usuarios:

- `admin@piso4.test`
- `mesero@piso4.test`
- `cocina@piso4.test`
- `bar@piso4.test`

Password demo:

- `password`

Seeders relevantes:

- `RolesAndPermissionsSeeder`
- `StaffUserSeeder`
- `SettingsSeeder`
- `MesaSeeder`
- `MenuSeeder`
- `DatabaseSeeder`

## 19. Pruebas existentes

El proyecto tiene pruebas Feature para:

- Autenticacion.
- Registro.
- Password reset.
- Confirmacion de password.
- Verificacion de email.
- Two factor challenge.
- Settings de perfil y seguridad.
- Flujo de entrada del cliente por mesa.
- Flujo de pedido del cliente.
- Flujo de pago del cliente.
- Panel admin.
- Gestion de mesas.
- Tableros staff.
- Broadcasting.
- Estadisticas.

Archivos destacados:

- `tests/Feature/Customer/TableEntryTest.php`
- `tests/Feature/Customer/OrderFlowTest.php`
- `tests/Feature/Customer/PaymentFlowTest.php`
- `tests/Feature/Staff/StaffBoardsTest.php`
- `tests/Feature/Admin/AdminPanelTest.php`
- `tests/Feature/Admin/MesaManagementTest.php`
- `tests/Feature/StatisticsTest.php`
- `tests/Feature/BroadcastingTest.php`

## 20. Cosas que el sistema no hace actualmente

Para evitar que una IA invente funcionalidades:

- No hay pasarela de pago automatica.
- No se validan transferencias automaticamente.
- No se suben comprobantes dentro del sistema.
- El comprobante se envia por WhatsApp externo.
- La confirmacion de pago la hace el personal manualmente.
- La reserva de mesa en la landing es un enlace de WhatsApp, no un modulo
  interno de reservas.
- El cliente no tiene cuenta de usuario; usa cookie de participante.
- El canal publico de cliente se basa en token de mesa, no autenticacion.

## 21. Mapa mental del flujo operativo

1. Admin crea categorias, productos y mesas.
2. Admin descarga o muestra QR de una mesa.
3. Cliente escanea QR.
4. Sistema abre/reutiliza sesion activa de esa mesa.
5. Cliente ingresa nombre.
6. Sistema crea participante y cookie.
7. Cliente navega el menu operativo.
8. Cliente arma carrito.
9. Cliente envia pedido.
10. Sistema crea order e items con snapshot.
11. Evento `order.placed` notifica a meseros y estaciones.
12. Cocina/bar empiezan y marcan listo cada item.
13. Evento `order.item.status` actualiza cliente y staff.
14. Mesero entrega items listos.
15. Cliente solicita cuenta.
16. Sistema crea bill y llamado tipo cuenta.
17. Cliente elige division y genera pagos.
18. Cliente elige metodo de pago.
19. Si es transferencia, abre WhatsApp con datos bancarios.
20. Mesero confirma pagos.
21. Si todos los pagos estan confirmados, cuenta queda pagada y pedidos
    quedan facturados.
22. Mesero cierra sesion de mesa.
23. Mesa vuelve a disponible.

## 22. Archivos clave para una IA

Si una IA necesita entender o modificar una funcionalidad, estos son los puntos
de entrada:

- Login y redireccion por rol:
  - `app/Providers/FortifyServiceProvider.php`
  - `app/Http/Responses/LoginResponse.php`
  - `app/Http/Controllers/DashboardController.php`
  - `app/Enums/RoleType.php`

- Cliente QR:
  - `routes/web.php`
  - `app/Http/Controllers/Customer/TableEntryController.php`
  - `app/Http/Middleware/EnsureParticipant.php`
  - `resources/views/customer/entry.blade.php`
  - `resources/views/pages/customer/⚡menu.blade.php`
  - `resources/views/pages/customer/⚡orders.blade.php`
  - `resources/views/pages/customer/⚡bill.blade.php`

- Pedidos:
  - `app/Services/CartService.php`
  - `app/Services/OrderService.php`
  - `app/Models/Order.php`
  - `app/Models/OrderItem.php`
  - `app/Events/OrderPlaced.php`
  - `app/Events/OrderItemStatusChanged.php`

- Cocina/bar:
  - `resources/views/pages/kitchen/⚡board.blade.php`
  - `resources/views/pages/bar/⚡board.blade.php`
  - `app/Concerns/HasStationBoard.php`
  - `resources/views/partials/station-board.blade.php`

- Mesero:
  - `resources/views/pages/waiter/⚡dashboard.blade.php`
  - `app/Services/WaiterService.php`
  - `app/Services/PaymentService.php`
  - `app/Services/SessionService.php`

- Cuenta y pagos:
  - `app/Services/BillService.php`
  - `app/Services/PaymentService.php`
  - `app/Services/WhatsApp/DeepLinkWhatsApp.php`
  - `app/Contracts/WhatsAppGateway.php`
  - `resources/views/pages/customer/⚡bill.blade.php`

- Admin:
  - `routes/admin.php`
  - `resources/views/pages/admin/⚡dashboard.blade.php`
  - `resources/views/pages/admin/⚡products.blade.php`
  - `resources/views/pages/admin/⚡categories.blade.php`
  - `resources/views/pages/admin/⚡users.blade.php`
  - `resources/views/pages/admin/⚡orders.blade.php`
  - `resources/views/pages/admin/⚡statistics.blade.php`
  - `resources/views/pages/admin/⚡settings.blade.php`
  - `app/Http/Controllers/Admin/MesaController.php`

- Tiempo real:
  - `routes/channels.php`
  - `resources/js/echo.js`
  - `app/Events/*`

- Visual:
  - `resources/css/app.css`
  - `resources/views/layouts/app/sidebar.blade.php`
  - `resources/views/layouts/customer.blade.php`
  - `resources/views/components/smoke-bg.blade.php`
  - `public/piso-cuatro-menu/*`

## 23. Notas de mantenimiento

- Antes de cambiar una regla de negocio, revisar primero el servicio
  correspondiente en `app/Services`.
- Antes de cambiar estados, revisar enum, migraciones, casts de modelos,
  filtros de vistas y tests.
- Antes de cambiar Reverb/Echo, revisar `resources/js/echo.js`,
  `routes/channels.php` y los eventos.
- Antes de cambiar menu operativo, distinguir entre:
  - menu publico de marketing en `/`;
  - menu operativo por QR en `/mesa/{token}/menu`.
- Antes de cambiar pagos, recordar que la venta real para estadisticas se mide
  por pagos confirmados, no por pedidos creados.
- Antes de cerrar una mesa, revisar que el flujo operativo espera que el mesero
  lo haga desde `/mesero`.
