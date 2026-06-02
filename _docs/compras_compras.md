# Compras

## Descripción

Registro de compras de mercancía a proveedores. Cada compra actualiza automáticamente el stock de los productos en la sucursal activa y genera un movimiento de entrada en el historial.

## Encabezado de la compra

| Campo | Descripción |
|-------|-------------|
| Proveedor | Proveedor al que se le compra |
| Fecha | Fecha de la compra |
| Folio / referencia | Número de factura o referencia del proveedor; opcional |
| Notas | Observaciones generales; opcional |

## Detalle de productos

Cada compra puede incluir varios productos:

| Campo | Descripción |
|-------|-------------|
| Producto | Producto del catálogo |
| Cantidad | Unidades compradas |
| Precio unitario | Precio de compra por unidad |
| Subtotal | Calculado automáticamente (cantidad × precio) |

## Comportamiento al guardar

- El stock de cada producto en la sucursal activa se incrementa con la cantidad comprada.
- Se genera un movimiento de **entrada** en el historial por cada producto.
- El sistema valida que la cantidad sea mayor a cero.

## Cancelación de compra

Al cancelar una compra:

- Los productos regresan al stock que tenían antes (se revierte la entrada).
- Se genera un movimiento de **salida** en el historial por cada producto.
- La compra queda marcada como "Cancelada" y ya no se puede modificar.

## Pestañas del módulo

| Pestaña | Contenido |
|---------|-----------|
| Compras activas | Lista de compras en estado activo |
| Compras canceladas | Lista de compras canceladas; con filtro de fechas (últimos 30 días por defecto) |

## Filtro por sucursal

Solo se muestran las compras de la sucursal activa en sesión.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver compras | ver |
| Registrar compra | crear |
| Cancelar compra | editar |
