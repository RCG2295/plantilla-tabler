# Movimientos de Inventario

## Descripción

Historial completo de entradas y salidas de inventario de la sucursal activa. Incluye movimientos registrados manualmente y los generados automáticamente por compras.

## Tipos de movimiento

| Tipo | Descripción |
|------|-------------|
| Entrada | Aumenta el stock (compra, devolución, ajuste positivo) |
| Salida | Disminuye el stock (venta, merma, ajuste negativo) |

## Columnas de la tabla

| Columna | Descripción |
|---------|-------------|
| Fecha | Fecha y hora del movimiento |
| Producto | Nombre y código del producto |
| Tipo | Entrada o Salida |
| Cantidad | Unidades movidas |
| Stock anterior | Stock antes del movimiento |
| Stock nuevo | Stock después del movimiento |
| Motivo | Razón del movimiento (ver Motivos de Movimiento) |
| Notas | Observaciones adicionales |
| Usuario | Quien registró el movimiento |

## Filtros disponibles

- **Producto**: filtrar por un producto específico.
- **Tipo**: mostrar solo entradas o solo salidas.
- **Fecha desde / hasta**: rango de fechas.

## Movimientos automáticos

Los siguientes eventos generan movimientos automáticamente:

| Evento | Tipo generado |
|--------|---------------|
| Alta de producto con stock inicial | Entrada |
| Registro de compra | Entrada por cada producto |
| Cancelación de compra | Salida (reverso) |

## Registrar movimiento manual

Desde el módulo de movimientos se puede registrar un movimiento manual seleccionando el producto, tipo, cantidad, motivo y notas. El sistema valida que haya stock suficiente en salidas.

## Filtro por sucursal

Solo se muestran los movimientos de la sucursal activa en sesión.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver historial | ver |
| Registrar movimiento manual | ver + editar (de productos) |
