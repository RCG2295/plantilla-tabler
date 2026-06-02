# Productos

## Descripción

Catálogo general de productos. El catálogo es **compartido** entre todas las sucursales, pero el **stock es independiente** por sucursal. Cada producto puede tener múltiples fotos y un historial de movimientos.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Código | Identificador único del producto (ej. SKU, código de barras) |
| Nombre | Nombre descriptivo del producto |
| Descripción | Descripción larga; opcional |
| Categoría | Clasificación del producto |
| Unidad de medida | Cómo se mide el producto (pieza, kg, litro, etc.) |
| Stock inicial | Solo al crear; define la cantidad inicial en la sucursal activa |
| Stock mínimo | Nivel de alerta de stock bajo |
| Stock máximo | Nivel de stock ideal |
| Precio de costo | Precio de compra |
| Precio de venta | Precio de venta normal |
| Se fracciona | Si el producto puede venderse por fracciones (ej. por gramo) |
| Cantidad por presentación | Unidades por presentación si se fracciona |
| Precio de venta por unidad | Precio por fracción si se fracciona |
| Estado | Activo o Inactivo |

## Stock por sucursal

El stock se guarda en una tabla separada (`inventario_stock`) con la combinación `(producto, sucursal)`. Al visualizar el catálogo, se muestra el stock de la **sucursal activa** en sesión.

Al agregar una sucursal nueva, el sistema inicializa su stock en cero para todos los productos existentes.

## Fotos

- Cada producto puede tener varias fotos.
- Una foto es marcada como **principal** y se muestra en la tabla.
- Formatos aceptados: JPG, PNG, WEBP, GIF. Máximo 2 MB por foto.
- Se puede cambiar la foto principal haciendo clic en el ícono de estrella.

## Historial de movimientos (detalle de producto)

Dentro del detalle de cada producto hay una pestaña con el historial de movimientos de esa sucursal: entradas, salidas, stock anterior y nuevo, motivo y usuario.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver catálogo | ver |
| Agregar producto | crear |
| Editar producto / registrar movimiento | editar |
| Eliminar producto | eliminar |
