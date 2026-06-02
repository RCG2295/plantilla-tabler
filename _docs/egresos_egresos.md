# Egresos

## Descripción

Registro de gastos y egresos de la empresa por sucursal. Permite llevar el control de los gastos operativos clasificados por categoría.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Categoría | Tipo de gasto |
| Concepto | Descripción del gasto |
| Monto | Cantidad en dinero |
| Fecha | Fecha del egreso |
| Notas | Observaciones adicionales; opcional |

## Cancelación de egresos

Un egreso puede marcarse como cancelado. Los egresos cancelados se muestran en la pestaña correspondiente y no se consideran en los totales.

## Pestañas del módulo

| Pestaña | Contenido |
|---------|-----------|
| Egresos activos | Lista de egresos en estado activo |
| Egresos cancelados | Lista de egresos cancelados; con filtro de fechas (últimos 30 días por defecto) |

## Filtro por sucursal

Solo se muestran los egresos de la sucursal activa en sesión.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver egresos | ver |
| Registrar egreso | crear |
| Cancelar egreso | editar |
| Eliminar egreso | eliminar |
