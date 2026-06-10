# Performance Review Skill - Restaurante App

## Objetivo
Revisar el aplicativo para detectar causas de lentitud en acciones del usuario, especialmente al presionar botones, enviar información, recibir respuestas, actualizar estados, abrir dropdowns, cargar vistas y procesar pedidos.

## Contexto del sistema
El aplicativo es un sistema web para restaurante. Incluye menú público por QR, pedidos de clientes, sesiones por mesa, panel de cocina, bar, meseros y administrador, comunicación en tiempo real, estados de pedidos, pagos y gestión de productos.

## Revisión obligatoria
Analiza el proyecto buscando problemas de rendimiento en:

1. Backend
- Consultas innecesarias a base de datos.
- Problemas N+1.
- Relaciones cargadas sin necesidad.
- Falta de eager loading.
- Métodos que consultan demasiadas veces.
- Procesos pesados ejecutados al presionar botones.
- Falta de paginación o límites.
- Consultas repetidas en componentes Livewire o controladores.
- Uso innecesario de refresh, reload o recalculado completo.
- Validaciones o procesos que bloquean la respuesta.

2. Frontend / Blade / Livewire
- Componentes que se renderizan completos por acciones pequeñas.
- Dropdowns lentos por cargar demasiada información.
- Botones que disparan demasiadas actualizaciones.
- Uso incorrecto de wire:model en tiempo real.
- Falta de wire:model.defer, lazy o debounce cuando aplique.
- Listas grandes sin paginación.
- Renderizado excesivo dentro de loops.
- Acciones que podrían usar wire:loading, disabled o estados temporales.
- Falta de wire:key en elementos repetidos.
- Eventos innecesarios que provocan rerenders.

3. Base de datos
- Consultas sin índices adecuados.
- Búsquedas por columnas frecuentes sin indexar.
- Ordenamientos pesados.
- Filtros sobre columnas usadas en pedidos, mesas, estados, pagos o sesiones.
- Tablas que podrían necesitar índices compuestos.

4. Tiempo real
- Eventos emitidos de forma excesiva.
- Actualizaciones globales cuando solo debería actualizarse una mesa, pedido o panel.
- Escucha innecesaria de eventos.
- Polling demasiado frecuente.
- Recarga completa de datos por cada evento.

5. Experiencia del usuario
- Botones sin estado de carga.
- Doble clic que genera acciones repetidas.
- Dropdowns que deberían cargar bajo demanda.
- Operaciones que deberían dividirse en pasos.
- Respuestas que podrían optimizarse sin cambiar la funcionalidad.

## Instrucciones importantes
No modifiques archivos al inicio.

Primero entrega un reporte con:
- Archivo afectado.
- Método, componente o vista relacionada.
- Causa probable de lentitud.
- Impacto en la experiencia del usuario.
- Prioridad: Alta, Media o Baja.
- Recomendación concreta.
- Riesgo de romper funcionalidad si se corrige.
- Cambio sugerido.

Después pregunta qué hallazgo debe corregirse primero.

Cuando corrijas:
- Corrige un solo problema a la vez.
- No refactorices partes no relacionadas.
- No cambies diseño visual sin autorización.
- No elimines funcionalidades.
- Muestra el diff o resumen exacto del cambio.
- Explica cómo probar si mejoró.