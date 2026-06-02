# Motivos de Movimiento

## Descripción

Catálogo de razones que pueden asociarse a un movimiento de inventario. Permiten clasificar y dar trazabilidad a los movimientos.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Nombre | Descripción del motivo (ej. "Compra a proveedor", "Merma", "Ajuste de inventario") |
| Estado | Activo o Inactivo |

## Motivos del sistema

Algunos motivos son generados automáticamente por el sistema:

| Motivo | Cuándo se usa |
|--------|---------------|
| Alta de producto | Al registrar un producto con stock inicial |
| Compra | Al registrar una compra (generado automáticamente) |
| Cancelación de compra | Al cancelar una compra |

## Uso en movimientos

Al registrar un movimiento manual, el usuario puede seleccionar un motivo del catálogo. El motivo es opcional pero recomendado para mantener trazabilidad.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver motivos | ver |
| Agregar motivo | crear |
| Editar motivo | editar |
| Eliminar motivo | eliminar |
