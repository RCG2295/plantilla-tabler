# Dashboard

## Descripcion
Panel principal del sistema. Muestra un resumen de la semana actual (lunes a domingo) filtrado por la sucursal activa del usuario. Cada widget se muestra o se oculta segun los permisos del rol.

## Widgets y permisos

| Widget | Permiso requerido |
|---|---|
| KPI Ventas semana | `ventas/historial_ventas` ver |
| KPI Turno activo | `ventas/mi_caja` ver |
| KPI Compras semana | `compras/compras` ver |
| KPI Egresos semana | `egresos/egresos` ver |
| Grafico ventas por dia | `ventas/historial_ventas` ver |
| Grafico formas de pago | `ventas/historial_ventas` ver |
| Tabla stock bajo | `inventario/productos` ver |
| Tabla ultimas ventas | `ventas/historial_ventas` ver |

## Comportamiento / Reglas importantes

- El periodo siempre es la semana actual (lunes a hoy). El domingo de la semana se usa como limite maximo en la BD, pero la consulta solo llega hasta `hoy`.
- La sucursal se lee de `$_SESSION['id_sucursal']`. Si es `null`, se muestran datos de todas las sucursales (superadmin sin sucursal asignada).
- El selector de sucursal no se duplica en el dashboard: el usuario lo cambia desde la navbar.
- El boton "Actualizar" destruye y re-renderiza los graficos ApexCharts para evitar duplicados.
- Si no hay ventas en la semana, el grafico de formas de pago muestra un mensaje de texto en lugar del donut.
- Los productos "bajo stock" son aquellos con `stock_actual <= stock_minimo` y `stock_minimo > 0`, ordenados por el ratio `stock_actual / stock_minimo` ascendente (los mas criticos primero).
- Si el usuario no tiene acceso a ninguna area, el dashboard queda vacio (solo el encabezado).

## Estados del turno activo

| Condicion | Lo que se muestra |
|---|---|
| Existe turno con estado=0 | Cajero, hora apertura, ventas en turno, total cobrado, fondo |
| No hay turno activo | Mensaje "Sin turno activo en esta sucursal" |

## Permisos requeridos (resumen)

No tiene permisos propios — delega a los permisos de cada modulo correspondiente. Un usuario con todos los permisos vera el dashboard completo; uno sin acceso a ventas no vera ninguna tarjeta ni grafico de ventas.
