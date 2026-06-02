# Tipo de cambio

## Descripción
Permite registrar y consultar el tipo de cambio vigente (USD a MXN). El valor más reciente registrado se usa automáticamente en todas las operaciones del área de Ventas que involucran dólares.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Valor (MXN por 1 USD) | Tipo de cambio a registrar. Se almacena con 4 decimales. |

## Comportamiento / Reglas importantes

- Solo se registra el tipo de cambio; no se edita ni elimina el historial.
- El sistema siempre usa el **último valor registrado**, sin importar qué tan antiguo sea.
- Cada nuevo registro genera una entrada en el historial con fecha y usuario.
- Si no hay tipo de cambio registrado, el POS no puede convertir pagos en dólares.

## Estados

| Estado | Significado |
|--------|-------------|
| 0 | Activo |
| 2 | Eliminado (no se usa por ahora) |

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver historial | `ventas/tipo_cambio` — ver |
| Registrar nuevo valor | `ventas/tipo_cambio` — crear |
