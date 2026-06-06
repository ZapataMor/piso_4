# Verificación Visual — Checklist de Diseño

Use este documento para verificar que todas las vistas se vean correctamente según el nuevo sistema de diseño.

---

## 🔐 Pantalla de Login

**URL**: `http://localhost:8000/login`

**Elementos a verificar:**

- [ ] Fondo negro (zinc-950)
- [ ] Logo con círculo amber claro
- [ ] Texto "PISO CUATRO" en amber pequeño
- [ ] Título "Acceder a Piso Cuatro" en blanco
- [ ] Inputs oscuros con border gris
- [ ] Botón "Acceder" en amber sólido
- [ ] Focus ring amber cuando escribas en inputs
- [ ] Link "¿Olvidaste tu contraseña?" en amber
- [ ] Responsive en móvil (ancho max)

**Credenciales de prueba:**
```
Email: admin@piso4.test
Password: password
```

---

## 🍽️ Flujo del Cliente (QR)

### Paso 1: Entrada a Mesa
**URL**: `http://localhost:8000/mesa/{qr_token}/`

- [ ] Pantalla para ingresar nombre
- [ ] Input oscuro (zinc-900)
- [ ] Botón submit en amber

### Paso 2: Menú
**URL**: `http://localhost:8000/mesa/{qr_token}/menu`

**Header:**
- [ ] "Mesa #" en amber pequeño (header-subtitle)
- [ ] "Hola, [nombre]" en blanco
- [ ] Botones "🔔 Mesero" en gris
- [ ] Botones "Pedidos" en gris
- [ ] Header sticky en top

**Contenido:**
- [ ] Categorías con kicker (subtítulo amber pequeño)
- [ ] Título categoría en blanco grande
- [ ] Productos como cards (border zinc-800)
- [ ] Nombre producto en blanco
- [ ] Descripción en gris
- [ ] Precio en amber bold
- [ ] Hover efectuado (border más claro)

**Modal Agregar:**
- [ ] Background overlay negro 70%
- [ ] Modal card oscura (zinc-900)
- [ ] Título y descripción
- [ ] Controles cantidad (+ -)
- [ ] Textarea para notas
- [ ] Botón "Cancelar" gris
- [ ] Botón "✓ Agregar" amber
- [ ] Animación suave al abrir/cerrar

**Bottom Bar:**
- [ ] Aparece cuando hay items en carrito
- [ ] Botón amber con contador y total
- [ ] Fondo oscuro con blur

**Drawer Carrito:**
- [ ] Abre desde abajo (bottom-sheet)
- [ ] Items con imagen del producto (si aplica)
- [ ] Nombre, notas, precio por item
- [ ] Controles cantidad (+ -)
- [ ] Botón borrar (✕) rojo
- [ ] Resumen total
- [ ] Botón "✓ Enviar Pedido" amber
- [ ] Cierra al hacer click afuera

### Paso 3: Mis Pedidos
**URL**: `http://localhost:8000/mesa/{qr_token}/pedidos`

- [ ] Header con "Mis pedidos"
- [ ] Cards de pedido con número
- [ ] Badge de estado (color-coded)
  - [ ] Verde = Listo
  - [ ] Ámbar = Pendiente
  - [ ] Rojo = Cancelado
- [ ] Items listados con notas
- [ ] Subtotal de pedido
- [ ] Link a menú

### Paso 4: Cuenta
**URL**: `http://localhost:8000/mesa/{qr_token}/cuenta`

**Paso 1 - Solicitar:**
- [ ] Total estimado grande en amber
- [ ] Botón "🔔 Solicitar cuenta" amber

**Paso 2 - Modalidad:**
- [ ] Cards de modalidad seleccionables
- [ ] Activa tiene border amber y fondo amber/10
- [ ] Descripción de cada modalidad
- [ ] Si es "personalizada":
  - [ ] Dropdown para asignar por persona
  - [ ] Resumen de cálculo

**Paso 3 - Pago:**
- [ ] Para cada persona:
  - [ ] Nombre
  - [ ] Monto en amber bold
  - [ ] Badge "✓ Confirmado" (verde) o "Pendiente" (ámbar)
  - [ ] 3 botones de método (Efectivo, Tarjeta, Transferencia)
- [ ] Si selecciona "Transferencia":
  - [ ] Input para nombre
  - [ ] Input para teléfono
  - [ ] Botón "Guardar datos"
  - [ ] Botón verde "💬 Enviar por WhatsApp" (después de guardar)

---

## 👨‍💼 Panel Mesero

**URL**: `http://localhost:8000/mesero` (requiere login como mesero@piso4.test)

### Header
- [ ] "PERSONAL" en amber pequeño
- [ ] "Panel de Meseros" en blanco grande
- [ ] Botón "↻ Actualizar" gris

### Sección Llamados (si hay)
- [ ] Fondo rojo oscuro (red-950/40)
- [ ] Border rojo (red-900)
- [ ] Título "🔔 Llamados urgentes"
- [ ] Badge rojo con count
- [ ] Cards por llamado:
  - [ ] "Mesa #"
  - [ ] Tipo, cliente, hora
  - [ ] Botón "Atender ahora" rojo
- [ ] Click desaparece de la lista

### Sección Cuentas (si hay)
- [ ] Fondo ámbar oscuro (amber-950/30)
- [ ] Border ámbar (amber-900)
- [ ] Título "💵 Cuentas pendientes"
- [ ] Badge ámbar con count
- [ ] Cards por cuenta:
  - [ ] "Mesa #"
  - [ ] Total en amber bold
  - [ ] Modalidad (Única/Automática/Personalizada)
  - [ ] Lista de pagos:
    - [ ] Nombre | Método | Monto
    - [ ] Si está confirmado: badge verde "✓"
    - [ ] Si no: botón "Confirmar" ámbar

### Sección Listos
- [ ] Grid 2 columnas en desktop
- [ ] Header: "📦 Listos para servir" + count
- [ ] Items listos:
  - [ ] Mesa #, cliente
  - [ ] Cantidad × Producto
  - [ ] Botón "Entregar" verde

### Sección Mesas Activas
- [ ] Grid 2 columnas en desktop
- [ ] Header: "🪑 Mesas activas" + count
- [ ] Cards por mesa:
  - [ ] "Mesa #"
  - [ ] "X personas · Y pedidos"
  - [ ] Total en ámbar
  - [ ] Botón "Cerrar mesa" gris

---

## 🍳 Panel Cocina

**URL**: `http://localhost:8000/cocina` (requiere login como cocina@piso4.test)

### Header
- [ ] "ESTACIÓN" en amber pequeño
- [ ] "Panel de Cocina" en blanco
- [ ] Botón "↻ Actualizar" gris

### Tablero 3 Columnas

#### Columna 1: Pendientes
- [ ] Header rojo (red-900, red-950/30)
- [ ] Título "Pendientes"
- [ ] Badge rojo con contador
- [ ] Items:
  - [ ] "Mesa # · cliente"
  - [ ] Hora en gris
  - [ ] "N× Producto" en bold
  - [ ] Notas en ámbar si hay
  - [ ] Botón "Empezar a preparar" ámbar

#### Columna 2: En Preparación
- [ ] Header ámbar (amber-900, amber-950/30)
- [ ] Título "En preparación"
- [ ] Badge ámbar con contador
- [ ] Items similares a pendientes
- [ ] Botón "✓ Marcar listo" verde

#### Columna 3: Listos
- [ ] Header verde (green-900, green-950/30)
- [ ] Título "Listos"
- [ ] Badge verde con contador
- [ ] Items con fondo verde claro
- [ ] Texto "✓ Listo para servir" verde
- [ ] Sin botones (esperando mesero)

**General:**
- [ ] Responsive: 1 columna móvil, 3 desktop
- [ ] Scroll vertical si hay muchos items
- [ ] Actualizaciones en tiempo real (Reverb)

---

## 🍹 Panel Bar

**URL**: `http://localhost:8000/bar` (requiere login como bar@piso4.test)

- [ ] Idéntico a Panel Cocina (comparte `station-board.blade.php`)
- [ ] Mismo layout 3 columnas
- [ ] Mismos estilos y comportamiento

---

## ⚙️ Panel Admin

**URL**: `http://localhost:8000/admin` (requiere login como admin@piso4.test)

### Header
- [ ] "SISTEMA" en amber pequeño
- [ ] "Panel de Control" en blanco grande
- [ ] Botón "↻ Actualizar" gris

### Métricas (Grid 4 columnas, responsive)
- [ ] **Ventas de hoy**
  - [ ] Ícono moneda en ámbar
  - [ ] Monto en grande
  - [ ] Label "Etiqueta" en gris
- [ ] **Mesas activas**
  - [ ] Ícono cuadrículas en azul
  - [ ] Número en grande
- [ ] **Ítems en preparación**
  - [ ] Ícono fuego en ámbar
  - [ ] Número en grande
- [ ] **Pedidos de hoy**
  - [ ] Ícono receipt en gris
  - [ ] Número en grande

### Accesos Rápidos (Grid 3 columnas, responsive)
- [ ] 7 tarjetas:
  1. [ ] Productos (ícono pastel)
  2. [ ] Categorías (ícono tag)
  3. [ ] Mesas y QR (ícono QR)
  4. [ ] Usuarios (ícono usuarios)
  5. [ ] Pedidos (ícono lista)
  6. [ ] Estadísticas (ícono gráfico)
  7. [ ] Configuración (ícono engranaje)

- [ ] Cada tarjeta:
  - [ ] Border gris oscuro
  - [ ] Ícono en ámbar
  - [ ] Nombre del link
  - [ ] Flecha derecha en hover
  - [ ] Hover efecto (border más claro, bg amber/5)

---

## 📱 Responsividad

Prueba en diferentes tamaños:

### Móvil (< 640px)
- [ ] Cliente: ancho completo, max-w-lg
- [ ] Header: botones apilados o redimensionados
- [ ] Modales: rounded-top en mobile
- [ ] Drawers: full height menos header
- [ ] Tablas: scroll horizontal

### Tablet (640px - 1024px)
- [ ] Grid 2 columnas donde aplique
- [ ] Sidebar Flux collapsible
- [ ] Layouts responsivos

### Desktop (> 1024px)
- [ ] Grid 3-4 columnas
- [ ] Sidebar visible
- [ ] Máximos anchos respetados

---

## 🎨 Verificación de Colores

Abre DevTools y verifica:

```javascript
// En consola, verificar colores computados
const el = document.querySelector('[class*="bg-amber"]');
window.getComputedStyle(el).backgroundColor;
// Debe ser: rgb(245, 158, 11) — amber-500
```

**Colores esperados:**
- `zinc-950` → `rgb(10, 10, 10)`
- `zinc-900` → `rgb(23, 23, 23)`
- `zinc-800` → `rgb(38, 38, 38)`
- `amber-500` → `rgb(245, 158, 11)`
- `amber-400` → `rgb(251, 191, 36)`

---

## 🔄 Tiempo Real (Reverb)

### Verificar conexión WebSocket

1. Abre DevTools (F12)
2. Pestaña **Network**
3. Filtra por **WS** (WebSocket)
4. Busca conexión a `ws://localhost:8080`
5. Debe estar en verde (connected)

### Probar actualización en vivo

**En Panel Mesero:**
1. Abre en navegador A
2. Abre en navegador B
3. En A, acciona "Atender llamado"
4. En B, debe desaparecer sin refrescar

---

## ♿ Accesibilidad

- [ ] Todos los inputs tienen labels
- [ ] Focus ring visible en inputs (amber)
- [ ] Buttons tienen tipos adecuados
- [ ] Contraste de colores ≥ 4.5:1 (WCAG AA)
  - [ ] Ámbar sobre negro: ✓ (OK)
  - [ ] Blanco sobre negro: ✓ (OK)
  - [ ] Gris sobre negro: ✓ (OK)
- [ ] Imágenes tienen alt text
- [ ] Aria-labels en botones sin texto

---

## 🐛 Errores Comunes a Buscar

- [ ] Colores light mode accidentales (blanco)
- [ ] Inputs sin border
- [ ] Botones sin hover state
- [ ] Modales que no cierran
- [ ] Texto ilegible por bajo contraste
- [ ] Elementos que se cortan en mobile
- [ ] Reverb no conecta (WebSocket)

---

## ✅ Checklist Final

Marca cuando todo esté verificado:

### Cliente (Mesa)
- [ ] Login
- [ ] Menú
- [ ] Modal agregar
- [ ] Carrito (drawer)
- [ ] Mis pedidos
- [ ] Cuenta (3 pasos)
- [ ] Responsive móvil

### Staff
- [ ] Panel Mesero (4 secciones)
- [ ] Panel Cocina (tablero)
- [ ] Panel Bar (tablero)
- [ ] Responsive desktop

### Admin
- [ ] Dashboard (métricas + accesos)
- [ ] Actualización manual
- [ ] Responsive grid

### General
- [ ] Colores correctos
- [ ] Tiempos real (Reverb)
- [ ] Accesibilidad
- [ ] Sin errores console

---

## 📞 Notas Finales

Si encuentras algo que no se ve correctamente:

1. Limpia caché: `Ctrl+Shift+R` (hard refresh)
2. Verifica console (F12 → Console)
3. Revisa que Reverb esté corriendo: `composer dev`
4. Compila assets: `npm run build`

**¡Listo para usar en producción!** 🚀
