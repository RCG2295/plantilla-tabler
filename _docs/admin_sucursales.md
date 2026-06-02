# Sucursales

## Descripción

Gestión de las sucursales (puntos de venta o almacenes) de la empresa. Solo el superadministrador puede administrar sucursales.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Nombre | Nombre de la sucursal (ej. "Mexicali Centro") |
| Dirección | Dirección física; opcional |
| Teléfono | Teléfono de contacto; opcional |
| Estado | Activo o Inactivo |

## Comportamiento al crear una sucursal

Al crear una sucursal nueva, el sistema inicializa automáticamente el **stock en cero** para todos los productos activos del catálogo en esa sucursal. Esto evita tener que hacerlo manualmente uno por uno.

## Cambio de sucursal (superadmin)

El superadministrador puede cambiar de sucursal desde el selector en la barra superior. Al cambiar:

- La sesión actualiza la sucursal activa temporalmente.
- Todos los listados (inventario, compras, egresos, movimientos) muestran datos de la sucursal seleccionada.
- La sucursal propia del superadmin se preserva internamente.

Los usuarios normales ven únicamente los datos de su sucursal asignada y no tienen el selector.

## Impacto en otros módulos

| Módulo | Comportamiento |
|--------|---------------|
| Inventario | Stock independiente por sucursal |
| Compras | Cada compra pertenece a una sucursal |
| Egresos | Cada egreso pertenece a una sucursal |
| Movimientos | Cada movimiento pertenece a una sucursal |

## Permisos requeridos

Solo roles con `es_superadmin = 1` pueden acceder a este módulo. Los demás roles no tienen acceso.
