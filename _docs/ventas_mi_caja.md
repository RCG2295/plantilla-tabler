# Mi caja

## Descripción
Vista única para todo lo relacionado con el turno del usuario en sesión. Permite abrir un turno, consultar el resumen de ventas y movimientos durante el turno activo, registrar retiros e ingresos de efectivo, y cerrar el turno generando el corte de caja.

## Estados de la vista

| Estado | Qué muestra |
|--------|-------------|
| Sin turno activo | Formulario para iniciar turno (fondo MXN, fondo USD, denominaciones opcionales). |
| Turno activo | Info del turno, resumen de ventas por forma de pago, efectivo esperado, y pestañas de ventas / movimientos. |

## Apertura de turno

| Campo | Descripción |
|-------|-------------|
| Fondo inicial en pesos (MXN) | Efectivo en pesos que se pone en caja al inicio del turno. |
| Fondo inicial en dólares (USD) | Efectivo en dólares al inicio del turno. |
| Denominaciones en pesos | Billetes y monedas (opcional, informativo). |
| Denominaciones en dólares | Billetes (opcional, informativo). |

## Resumen mostrado (turno activo)

| Dato | Descripción |
|------|-------------|
| Número de ventas | Total de ventas realizadas en el turno (sin incluir canceladas). |
| Total ventas (MXN) | Suma de los totales de ventas activas del turno. |
| Efectivo MXN | Total cobrado en efectivo en pesos. |
| Efectivo USD | Total cobrado en efectivo en dólares. |
| Tarjeta | Total cobrado con tarjeta. |
| Transferencia | Total cobrado por transferencia. |
| Efectivo esperado MXN | `fondo_pesos + efectivo_pesos + ingresos_pesos - retiros_pesos`. |
| Efectivo esperado USD | `fondo_dolares + efectivo_dolares + ingresos_dolares - retiros_dolares`. |

## Movimientos de caja

| Campo | Descripción |
|-------|-------------|
| Tipo | `retiro` o `ingreso`. |
| Moneda | `pesos` (MXN) o `dolares` (USD). |
| Monto | Cantidad del movimiento. |
| Descripción | Motivo del movimiento (opcional). |

## Cierre de turno

Al pulsar "Cerrar turno" se abre un modal con:

| Campo | Descripción |
|-------|-------------|
| Efectivo declarado en pesos | Monto contado físicamente en caja al cierre. |
| Efectivo declarado en dólares | Dólares contados al cierre. |
| Denominaciones al cierre | Billetes y monedas (opcional, informativo). |

El cierre calcula: `efectivo_esperado = fondo + ventas en efectivo + ingresos - retiros`. La diferencia (declarado - esperado) se guarda pero **no se muestra al cajero**. El tipo de cambio vigente al momento del cierre se guarda en el corte.

## Comportamiento / Reglas importantes

- Solo un turno activo por usuario a la vez.
- Los retiros e ingresos afectan el cálculo del efectivo esperado en caja.
- Las denominaciones son informativas y no afectan cálculos.
- El historial de todos los turnos se consulta desde **Historial de turnos** (`ventas/historial_turnos`).

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver resumen, abrir y cerrar turno | `ventas/mi_caja` — ver |
| Registrar retiro/ingreso | `ventas/mi_caja` — editar |
