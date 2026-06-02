# Categorías de Inventario

## Descripción

Clasificación jerárquica de los productos del inventario. Las categorías tienen dos niveles: **categoría padre** y **subcategoría**.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Nombre | Nombre de la categoría |
| Categoría padre | Si se selecciona, esta categoría es una subcategoría |
| Estado | Activo o Inactivo |

## Jerarquía

- Una categoría sin padre es de **primer nivel**.
- Una categoría con padre es una **subcategoría**.
- Los productos se asignan a cualquier nivel de la jerarquía.

## Uso en productos

Al crear o editar un producto, se puede asignar una categoría desde el catálogo de categorías activas.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver categorías | ver |
| Agregar categoría | crear |
| Editar categoría | editar |
| Eliminar categoría | eliminar |
