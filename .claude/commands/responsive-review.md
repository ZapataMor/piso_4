# Responsive Review Skill - Restaurante App

## Objetivo

Revisar el aplicativo web en modo responsive para detectar y corregir errores visuales en móvil, tablet y escritorio, sin cambiar la identidad visual ni rediseñar el sistema completo.

## Contexto del sistema

El aplicativo es un sistema web para restaurante construido con Laravel, Livewire, Blade, Tailwind/CSS y Alpine. Incluye menú público, carrito, pedidos, cuenta, pagos, panel de cocina/bar, panel de meseros, dashboard administrador, estadísticas, productos y usuarios.

## Revisión obligatoria

Analiza el código buscando problemas responsive en:

1. Vistas del cliente

* Menú público.
* Carrito.
* Pedidos realizados.
* Cuenta/bill.
* Modalidades de pago.
* Formularios de nombre, teléfono y transferencia.
* Botones principales.
* Listas de productos.
* Cards, modales y dropdowns.

2. Paneles internos

* Cocina.
* Bar.
* Mesero.
* Administrador.
* Estadísticas.
* Productos.
* Usuarios.
* Tablas, filtros, chips, cards y modales.

3. Problemas visuales a detectar

* Overflow horizontal.
* Botones cortados.
* Textos desbordados.
* Tablas que no caben en móvil.
* Cards demasiado anchas.
* Modales que no se adaptan.
* Inputs difíciles de tocar en móvil.
* Espaciados excesivos o muy pequeños.
* Elementos tapados por headers fijos.
* Dropdowns mal posicionados.
* Layouts que se rompen entre 320px y 768px.
* Uso incorrecto de grid, flex, min-width, width fija o padding.
* Falta de clases responsive como sm:, md:, lg:.
* Tablas que deberían convertirse en cards o scroll horizontal.
* Imágenes o iconos con tamaños rígidos.

## Tamaños de pantalla a considerar

Revisa pensando en estos anchos:

* 320px: móvil pequeño.
* 375px: móvil común.
* 430px: móvil grande.
* 768px: tablet.
* 1024px: laptop pequeña.
* 1366px: escritorio.

## Instrucciones importantes

No modifiques archivos al inicio.

Primero entrega un diagnóstico con:

* Archivo afectado.
* Vista o componente.
* Problema responsive probable.
* Tamaño de pantalla donde podría fallar.
* Causa técnica probable.
* Cambio recomendado.
* Riesgo de afectar el diseño actual.
* Prioridad: Alta, Media o Baja.

Después pregunta qué problema quiero corregir primero.

Cuando corrijas:

* Corrige un solo problema a la vez.
* No rediseñes toda la pantalla.
* No cambies colores, fuentes ni estilo general sin autorización.
* No cambies lógica Livewire, eventos ni backend.
* No modifiques archivos no relacionados.
* Muestra el diff final.
* Explica cómo probar manualmente el cambio en móvil, tablet y escritorio.

## Prioridad

Prioriza primero:

1. Menú público del cliente.
2. Carrito.
3. Pedidos realizados.
4. Cuenta/bill.
5. Panel mesero.
6. Cocina/bar.
7. Dashboard administrador.
8. Productos, usuarios y estadísticas.

## Resultado esperado

Quiero una revisión visual/responsive seria, no un rediseño. El objetivo es que la app se vea bien y sea usable en celulares, tablets y escritorio sin romper funcionalidades existentes.
