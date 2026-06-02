# Punto de venta (POS)

## Descripción
Interfaz de pantalla completa (sin sidebar ni navbar) para realizar ventas. Requiere un turno de caja activo para operar. Permite buscar productos, armar el carrito, registrar el pago con múltiples formas de pago y generar un ticket imprimible automáticamente.

## Requisitos previos

- El usuario debe tener un turno activo en Control de caja.
- Debe existir al menos un tipo de cambio registrado para aceptar pagos en dólares.
- Solo se muestran productos con `precio_venta > 0` o con `precio_venta_unidad > 0` si el producto se fracciona.

## Productos y carrito

| Elemento | Descripción |
|----------|-------------|
| Grid de productos | Tarjetas con imagen, nombre, precio y stock disponible. |
| Filtro por categoría | Chips horizontales que filtran el grid. |
| Búsqueda por texto | Filtra por nombre o código del producto. |
| Productos sin stock | Se muestran deshabilitados (no se pueden agregar). |
| Productos fraccionables | Al agregarlos se pregunta: vender por presentación o por unidad (con su precio correspondiente). |
| Ajuste de cantidad | Botones +/- en el carrito; también editable directamente. |

## Formas de pago

| Forma | Descripción |
|-------|-------------|
| Efectivo MXN | Se permite siempre. |
| Efectivo USD | Solo disponible si el pago en pesos no cubre el total. Se convierte con el tipo de cambio vigente. |
| Tarjeta | No puede superar el total. |
| Transferencia | No puede superar el total. |

## Comportamiento / Reglas importantes

- El botón **Cobrar** se activa solo cuando el total pagado cubre el total.
- El cambio siempre se muestra en pesos MXN.
- Tarjeta y transferencia no pueden exceder el total individualmente.
- Si el efectivo en pesos ya cubre el total, el campo de efectivo en dólares se deshabilita.
- Al registrar la venta se descuenta el stock y se registra un movimiento de inventario (tipo `salida`, motivo `Venta`).
- Después de cobrar se imprime el ticket automáticamente (ventana de impresión del navegador).
- El ticket es de 80 mm de ancho y se imprime en fuente monoespaciada.

## Ticket

El ticket incluye: nombre de la sucursal, folio (formato `000001`), fecha/hora, cajero, tabla de productos (nombre, tipo, cantidad, precio unitario, subtotal), total, formas de pago y leyenda de agradecimiento.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Abrir el POS y ver productos | `ventas/pos` — ver |
| Registrar venta | `ventas/pos` — crear |
| Cancelar venta | `ventas/pos` — eliminar |
