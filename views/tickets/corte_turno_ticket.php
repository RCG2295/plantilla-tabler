<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_ventas_caja.php';
require_once __DIR__ . '/../../models/model_ventas_tipo_cambio.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo '<p>No autorizado.</p>';
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    echo '<p>ID de turno no válido.</p>';
    exit;
}

$model  = new VentasCajaModel();
$turno  = $model->getById($id);

if (!$turno) {
    echo '<p>Turno no encontrado.</p>';
    exit;
}

$corte        = $model->getCorte($id);
$den_apertura = $model->getDenominaciones($id, 'apertura');
$den_cierre   = $model->getDenominaciones($id, 'cierre');
$movimientos  = $model->getMovimientos($id);
$den_fijas    = VentasCajaModel::getDenominacionesFijas();
$app_url      = rtrim($_ENV['APP_URL'] ?? '', '/');

$cajero       = trim(($turno['usuario_nombre'] ?? '') . ' ' . ($turno['usuario_apellidos'] ?? '')) ?: 'Sistema';
$sucursal     = $turno['sucursal_nombre'] ?? '—';
$apertura_fmt = $turno['fecha_alta']   ? date('d/m/Y H:i', strtotime($turno['fecha_alta']))   : '—';
$cierre_fmt   = $turno['fecha_cierre'] ? date('d/m/Y H:i', strtotime($turno['fecha_cierre'])) : '—';

function fmt_num(float $n, int $dec = 2): string {
    return '$' . number_format($n, $dec, '.', ',');
}

function render_denominaciones(array $dens, string $moneda, array $fijas): string {
    // Index recorded quantities by "tipo|denominacion"
    $ingresado = [];
    foreach ($dens as $d) {
        if ($d['moneda'] !== $moneda) continue;
        $ingresado[$d['tipo'] . '|' . $d['denominacion']] = (int) $d['cantidad'];
    }

    $grupos = $moneda === 'pesos'
        ? [['tipo' => 'billete', 'items' => $fijas['pesos']['billetes']], ['tipo' => 'moneda', 'items' => $fijas['pesos']['monedas']]]
        : [['tipo' => 'billete', 'items' => $fijas['dolares']['billetes']]];

    $prefijo = $moneda === 'pesos' ? '$' : 'USD ';
    $html    = '<table class="den-table"><thead><tr><th>Tipo</th><th>Denominación</th><th>Cantidad</th><th class="text-end">Subtotal</th></tr></thead><tbody>';
    $total   = 0;

    foreach ($grupos as $g) {
        foreach ($g['items'] as $den) {
            $key = $g['tipo'] . '|' . $den;
            $qty = $ingresado[$key] ?? 0;
            $sub = $den * $qty;
            $total += $sub;
            $style = $qty === 0 ? ' style="color:#bbb;"' : '';
            $html .= '<tr' . $style . '>';
            $html .= '<td>' . ucfirst($g['tipo']) . '</td>';
            $html .= '<td>' . $prefijo . number_format((float)$den, 2) . '</td>';
            $html .= '<td>' . $qty . '</td>';
            $html .= '<td class="text-end">' . ($qty > 0 ? $prefijo . number_format($sub, 2) : '<span style="color:#bbb;">—</span>') . '</td>';
            $html .= '</tr>';
        }
    }

    $html .= '<tr class="den-total"><td colspan="3">Total</td><td class="text-end">' . $prefijo . number_format($total, 2) . '</td></tr>';
    $html .= '</tbody></table>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Corte de caja — Turno #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #f5f7fa;
        }

        .ticket-wrapper {
            max-width: 860px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,.10);
            overflow: hidden;
        }

        /* Header */
        .ticket-header {
            background: #1b8ea3;
            color: #fff;
            padding: 22px 32px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .ticket-header h1 { font-size: 1.35rem; font-weight: 700; letter-spacing: .4px; }
        .ticket-header .folio { font-size: .95rem; opacity: .85; margin-top: 4px; }

        /* Meta blocks */
        .ticket-meta {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #e8eaf0;
        }
        .meta-block {
            flex: 1 1 25%;
            padding: 13px 28px;
            border-right: 1px solid #e8eaf0;
        }
        .meta-block:last-child { border-right: none; }
        .meta-label { font-size: 0.7rem; text-transform: uppercase; color: #6c757d; letter-spacing: .5px; margin-bottom: 3px; }
        .meta-value { font-weight: 600; font-size: 0.88rem; }

        /* Body */
        .ticket-body { padding: 22px 32px; }

        /* Section */
        .section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #6c757d;
            font-weight: 700;
            border-bottom: 1px solid #e8eaf0;
            padding-bottom: 5px;
            margin-bottom: 12px;
            margin-top: 22px;
        }
        .section-title:first-child { margin-top: 0; }

        /* Grid de datos */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 12px 20px;
            margin-bottom: 4px;
        }
        .data-item .di-label { font-size: 0.72rem; color: #6c757d; margin-bottom: 2px; }
        .data-item .di-value { font-weight: 600; font-size: 0.92rem; }
        .data-item .di-value.accent { color: #1b8ea3; }
        .data-item .di-value.danger { color: #dc3545; }
        .data-item .di-value.success { color: #198754; }

        /* Denominaciones */
        .den-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .den-block-title { font-size: 0.78rem; font-weight: 600; color: #495057; margin-bottom: 6px; }
        table.den-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        table.den-table thead th { background: #f0f4f8; padding: 5px 8px; text-align: left; color: #6c757d; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .3px; }
        table.den-table thead th.text-end { text-align: right; }
        table.den-table tbody td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; }
        table.den-table tbody td.text-end { text-align: right; }
        table.den-table tr.den-total td { font-weight: 700; border-top: 1px solid #dee2e6; padding-top: 7px; background: #f8f9fa; }

        /* Movimientos */
        table.mov-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        table.mov-table thead th { background: #f0f4f8; padding: 6px 10px; text-align: left; color: #6c757d; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .3px; }
        table.mov-table thead th.text-end { text-align: right; }
        table.mov-table tbody td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; }
        table.mov-table tbody td.text-end { text-align: right; }
        table.mov-table tbody tr:last-child td { border-bottom: none; }
        .badge-retiro  { background: #fee2e2; color: #dc3545; padding: 2px 7px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
        .badge-ingreso { background: #dcfce7; color: #16a34a; padding: 2px 7px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }

        /* Separador de cierre */
        .cierre-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }
        .cierre-col { padding: 14px 18px; border: 1px solid #e8eaf0; border-radius: 6px; }
        .cierre-col + .cierre-col { margin-left: 12px; }
        .cierre-col .cc-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; margin-bottom: 10px; font-weight: 700; }
        .cierre-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.88rem; border-bottom: 1px solid #f0f0f0; }
        .cierre-row:last-child { border-bottom: none; }
        .cierre-row .cr-label { color: #6c757d; }
        .cierre-row .cr-value { font-weight: 600; }
        .cierre-row.total-row { margin-top: 4px; padding-top: 8px; border-top: 2px solid #dee2e6; border-bottom: none; }
        .cierre-row.total-row .cr-label { font-weight: 700; }
        .cierre-row.total-row .cr-value { font-size: 1.05rem; color: #1b8ea3; }
        .diff-pos { color: #198754; }
        .diff-neg { color: #dc3545; }

        /* Footer */
        .ticket-footer {
            padding: 12px 32px;
            background: #f8f9fa;
            border-top: 1px solid #e8eaf0;
            font-size: 0.75rem;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
        }

        /* Botón imprimir */
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1b8ea3;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin: 20px 32px;
        }
        .btn-print:hover { background: #157a8e; }

        @media print {
            body { background: #fff; }
            .ticket-wrapper { box-shadow: none; margin: 0; border-radius: 0; max-width: 100%; }
            .btn-print { display: none !important; }
            .cierre-col { border-color: #ccc; }
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">

    <!-- Header -->
    <div class="ticket-header">
        <div>
            <h1>Corte de caja</h1>
            <div class="folio">Turno #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></div>
        </div>
        <img src="<?= $app_url ?>/views/assets/img/logo.png"
             alt="Logo"
             style="height:52px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
    </div>

    <!-- Meta -->
    <div class="ticket-meta">
        <div class="meta-block">
            <div class="meta-label">Cajero</div>
            <div class="meta-value"><?= htmlspecialchars($cajero) ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Sucursal</div>
            <div class="meta-value"><?= htmlspecialchars($sucursal) ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Apertura</div>
            <div class="meta-value"><?= $apertura_fmt ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Cierre</div>
            <div class="meta-value"><?= $cierre_fmt ?></div>
        </div>
    </div>

    <div class="ticket-body">

        <!-- Fondo inicial -->
        <div class="section-title">Fondo inicial</div>
        <div class="data-grid" style="grid-template-columns: repeat(2, 1fr); max-width: 360px;">
            <div class="data-item">
                <div class="di-label">Fondo en pesos (MXN)</div>
                <div class="di-value accent"><?= fmt_num((float)$turno['fondo_pesos']) ?></div>
            </div>
            <div class="data-item">
                <div class="di-label">Fondo en dólares (USD)</div>
                <div class="di-value accent">$<?= number_format((float)$turno['fondo_dolares'], 2) ?></div>
            </div>
        </div>

        <!-- Denominaciones de apertura -->
        <div class="section-title">Denominaciones de apertura</div>
        <div class="den-section">
            <div>
                <div class="den-block-title">Pesos (MXN)</div>
                <?= render_denominaciones($den_apertura, 'pesos', $den_fijas) ?>
            </div>
            <div>
                <div class="den-block-title">Dólares (USD)</div>
                <?= render_denominaciones($den_apertura, 'dolares', $den_fijas) ?>
            </div>
        </div>

        <!-- Resumen de ventas -->
        <div class="section-title">Resumen de ventas</div>
        <?php if ($corte): ?>
        <div class="data-grid">
            <div class="data-item">
                <div class="di-label">Total ventas</div>
                <div class="di-value"><?= fmt_num((float)$corte['total_ventas']) ?></div>
            </div>
            <div class="data-item">
                <div class="di-label">Efectivo MXN</div>
                <div class="di-value"><?= fmt_num((float)$corte['total_efectivo_pesos']) ?></div>
            </div>
            <div class="data-item">
                <div class="di-label">Efectivo USD</div>
                <div class="di-value">$<?= number_format((float)$corte['total_efectivo_dolares'], 2) ?></div>
            </div>
            <div class="data-item">
                <div class="di-label">Tarjeta</div>
                <div class="di-value"><?= fmt_num((float)$corte['total_tarjeta']) ?></div>
            </div>
            <div class="data-item">
                <div class="di-label">Transferencia</div>
                <div class="di-value"><?= fmt_num((float)$corte['total_transferencia']) ?></div>
            </div>
            <div class="data-item">
                <div class="di-label">Tipo de cambio usado</div>
                <div class="di-value">$<?= number_format((float)$corte['tipo_cambio_usado'], 2) ?> MXN/USD</div>
            </div>
        </div>
        <?php else: ?>
        <p style="color:#aaa;font-size:.85rem;">Sin corte registrado.</p>
        <?php endif; ?>

        <!-- Movimientos -->
        <?php if ($movimientos): ?>
        <div class="section-title">Movimientos de caja (retiros / ingresos)</div>
        <table class="mov-table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Moneda</th>
                    <th class="text-end">Monto</th>
                    <th>Descripción</th>
                    <th>Registrado por</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td>
                        <?php if ($m['tipo'] === 'retiro'): ?>
                            <span class="badge-retiro">Retiro</span>
                        <?php else: ?>
                            <span class="badge-ingreso">Ingreso</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $m['moneda'] === 'pesos' ? 'MXN' : 'USD' ?></td>
                    <td class="text-end">$<?= number_format((float)$m['monto'], 2) ?></td>
                    <td><?= htmlspecialchars($m['descripcion'] ?? '') ?: '<span style="color:#aaa;">—</span>' ?></td>
                    <td><?= htmlspecialchars($m['usuario'] ?? '—') ?></td>
                    <td><?= $m['fecha_alta'] ? date('d/m/Y H:i', strtotime($m['fecha_alta'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Cierre -->
        <?php if ($corte): ?>
        <div class="section-title">Cuadre de cierre</div>
        <div class="cierre-grid">
            <div class="cierre-col">
                <div class="cc-title">Pesos (MXN)</div>
                <div class="cierre-row">
                    <span class="cr-label">Efectivo esperado</span>
                    <span class="cr-value"><?= fmt_num((float)$corte['efectivo_esperado_pesos']) ?></span>
                </div>
                <div class="cierre-row">
                    <span class="cr-label">Declarado</span>
                    <span class="cr-value"><?= fmt_num((float)$corte['efectivo_declarado_pesos']) ?></span>
                </div>
                <?php $dif_p = (float)$corte['diferencia_pesos']; ?>
                <div class="cierre-row total-row">
                    <span class="cr-label">Diferencia</span>
                    <span class="cr-value <?= $dif_p < 0 ? 'diff-neg' : 'diff-pos' ?>">
                        <?= fmt_num($dif_p) ?>
                    </span>
                </div>
            </div>
            <div class="cierre-col" style="margin-left:12px;">
                <div class="cc-title">Dólares (USD)</div>
                <div class="cierre-row">
                    <span class="cr-label">Efectivo esperado</span>
                    <span class="cr-value">$<?= number_format((float)$corte['efectivo_esperado_dolares'], 2) ?></span>
                </div>
                <div class="cierre-row">
                    <span class="cr-label">Declarado</span>
                    <span class="cr-value">$<?= number_format((float)$corte['efectivo_declarado_dolares'], 2) ?></span>
                </div>
                <?php $dif_d = (float)$corte['diferencia_dolares']; ?>
                <div class="cierre-row total-row">
                    <span class="cr-label">Diferencia</span>
                    <span class="cr-value <?= $dif_d < 0 ? 'diff-neg' : 'diff-pos' ?>">
                        $<?= number_format($dif_d, 2) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Denominaciones de cierre -->
        <?php if ($corte): ?>
        <div class="section-title">Denominaciones al cierre</div>
        <div class="den-section">
            <div>
                <div class="den-block-title">Pesos (MXN)</div>
                <?= render_denominaciones($den_cierre, 'pesos', $den_fijas) ?>
            </div>
            <div>
                <div class="den-block-title">Dólares (USD)</div>
                <?= render_denominaciones($den_cierre, 'dolares', $den_fijas) ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="ticket-footer">
        <span>Turno <strong>#<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></strong> &nbsp;|&nbsp; <?= htmlspecialchars($cajero) ?></span>
        <span>Generado el <?= date('d/m/Y H:i') ?></span>
    </div>

</div>

<div style="text-align:center;">
    <button class="btn-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        Imprimir corte
    </button>
</div>

</body>
</html>
