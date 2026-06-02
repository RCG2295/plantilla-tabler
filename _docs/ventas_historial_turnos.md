# Historial de turnos

## Descripción
Muestra el registro completo de todos los turnos de caja del sistema, con posibilidad de consultar el corte de cada turno cerrado. Es un módulo independiente para controlar quién puede ver el historial de turnos.

## Columnas de la tabla

| Columna | Descripción |
|---------|-------------|
| # | ID del turno |
| Usuario | Cajero que abrió el turno |
| Sucursal | Sucursal asociada al turno |
| Fondo MXN | Fondo inicial declarado en pesos |
| Fondo USD | Fondo inicial declarado en dólares |
| Apertura | Fecha y hora de inicio del turno |
| Cierre | Fecha y hora de cierre (vacío si activo) |
| Estado | Activo / Cerrado |

## Corte de caja

Al presionar el botón de corte en un turno cerrado se muestra un resumen con:
- Total de ventas y desglose por forma de pago
- Tipo de cambio usado al cerrar
- Efectivo esperado vs declarado (pesos y dólares)
- Diferencia (positiva o negativa)

## Comportamiento / Reglas importantes

- Solo se puede ver el corte de turnos en estado **Cerrado**.
- La diferencia entre declarado y esperado se registra siempre; si es negativa se resalta en rojo.
- Este módulo es de **solo lectura** — no permite modificar ni cerrar turnos.
- La gestión de apertura/cierre se hace desde **Control de caja** (`ventas/caja`).

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver historial y cortes | `ventas/historial_turnos` — ver |
