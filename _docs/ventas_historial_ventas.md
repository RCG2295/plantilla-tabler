# Historial de ventas

## Descripción
Consulta de todas las ventas del sistema filtradas por rango de fecha. Muestra el desglose por forma de pago y permite imprimir el ticket de cualquier venta desde aquí. No carga datos hasta que se ingrese un rango de fechas.

## Columnas de la tabla

| Columna | Descripción |
|---------|-------------|
| Folio | Número de la venta en formato `000001`. |
| Cajero | Usuario que registró la venta. |
| Sucursal | Sucursal donde se realizó. |
| Turno | Número del turno de caja asociado. |
| Total | Importe total de la venta. |
| Ef. MXN | Efectivo cobrado en pesos. |
| Ef. USD | Efectivo cobrado en dólares. |
| Tarjeta | Monto cobrado con tarjeta. |
| Transferencia | Monto cobrado por transferencia. |
| Fecha | Fecha y hora de la venta. |
| Estado | Activa o Cancelada. |

## Comportamiento / Reglas importantes

- Las tablas no cargan datos hasta presionar **Buscar** con un rango de fechas válido.
- Las ventas están separadas en dos tabs: **Activas** y **Canceladas**.
- El tab Activas muestra un resumen en la barra de filtros con el conteo y el importe total.
- El botón de ticket abre una ventana con el detalle completo de la venta (disponible en ambos tabs).
- El filtro aplica sobre la fecha de apertura de la venta (`fecha_alta`).
- Al buscar se recargan ambas tablas simultáneamente.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver historial e imprimir tickets | `ventas/historial_ventas` — ver |
