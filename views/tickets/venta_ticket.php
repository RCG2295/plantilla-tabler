<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_ventas_pos.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo '<p>No autorizado.</p>';
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    echo '<p>ID de venta no válido.</p>';
    exit;
}

$model = new VentasPosModel();
$venta = $model->getVentaParaTicket($id);

if (!$venta) {
    echo '<p>Venta no encontrada.</p>';
    exit;
}

$app_url   = rtrim($_ENV['APP_URL'] ?? '', '/');
$cajero    = trim(($venta['cajero'] ?? '') . ' ' . ($venta['cajero_apellidos'] ?? '')) ?: 'Sistema';
$sucursal  = $venta['sucursal_nombre'] ?? '—';
$fecha_fmt = $venta['fecha_alta'] ? date('d/m/Y H:i', strtotime($venta['fecha_alta'])) : '—';
$cancelada = (int) $venta['estado'] === 1;

$labels = [
    'efectivo_pesos'   => 'Efectivo MXN',
    'efectivo_dolares' => 'Efectivo USD',
    'tarjeta'          => 'Tarjeta',
    'transferencia'    => 'Transferencia',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket de venta — <?= htmlspecialchars($venta['folio']) ?></title>
    <?php if (!$cancelada && puedo('ventas/pos', 'eliminar')): ?>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.css">
    <?php endif; ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #f5f7fa;
        }

        .ticket-wrapper {
            max-width: 820px;
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
        .badge-cancelada {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 10px;
            vertical-align: middle;
        }

        /* Meta */
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

        /* Section title */
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

        /* Tabla items */
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        table.items thead th {
            background: #f0f4f8;
            padding: 7px 10px;
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: .4px;
            border-bottom: 2px solid #dee2e6;
        }
        table.items thead th.text-end { text-align: right; }
        table.items tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        table.items tbody td.text-end { text-align: right; }
        table.items tbody tr:last-child td { border-bottom: none; }
        .prod-code { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }

        /* Totales y pagos */
        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-top: 16px;
            border-top: 2px solid #e8eaf0;
            padding-top: 14px;
        }
        .pagos-block { flex: 1; }
        .pagos-block .pago-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 0.85rem; border-bottom: 1px solid #f0f0f0; }
        .pagos-block .pago-row:last-child { border-bottom: none; }
        .pagos-block .pago-row .pr-label { color: #6c757d; }
        .pagos-block .pago-row .pr-value { font-weight: 600; }
        .totales-block { min-width: 200px; }
        .totales-block table { width: 100%; border-collapse: collapse; }
        .totales-block td { padding: 3px 8px; font-size: 0.88rem; }
        .totales-block td:last-child { text-align: right; font-weight: 600; }
        .totales-block tr.total-final td { font-size: 1.05rem; font-weight: 700; color: #1b8ea3; border-top: 1px solid #dee2e6; padding-top: 8px; }

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

        .btn-cancelar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin: 20px 0 20px 12px;
        }
        .btn-cancelar:hover { background: #bb2d3b; }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                background: #fff;
                font-family: 'Courier New', monospace;
                font-size: 8pt;
                color: #000;
                width: 80mm;
                margin: 0;
                padding: 0;
            }

            .btn-print, .btn-cancelar { display: none !important; }

            .ticket-wrapper {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }

            /* Header */
            .ticket-header {
                background: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: #fff !important;
                padding: 3mm;
                display: block;
                text-align: center;
                width: 100%;
                box-sizing: border-box;
            }
            .ticket-header h1   { font-size: 10pt; margin-bottom: 1mm; }
            .ticket-header .folio { font-size: 8pt; opacity: 1; }
            .ticket-header img  { height: 18px; margin-bottom: 2mm; filter: brightness(0) invert(1); }
            .badge-cancelada    { font-size: 6pt; }

            /* Meta */
            .ticket-meta        { display: block; border-bottom: 1px dashed #000; width: 100%; box-sizing: border-box; }
            .meta-block         { display: flex; justify-content: space-between; flex: none; width: 100%; padding: 1mm 3mm; border-right: none; border-bottom: none; box-sizing: border-box; }
            .meta-label         { font-size: 6.5pt; color: #000; text-transform: none; letter-spacing: 0; }
            .meta-value         { font-size: 7.5pt; }

            /* Body */
            .ticket-body        { padding: 2mm 3mm; width: 100%; box-sizing: border-box; }
            .section-title      { font-size: 6.5pt; letter-spacing: 0; margin-top: 3mm; margin-bottom: 1mm; border-bottom: 1px dashed #000; }
            .section-title:first-child { margin-top: 0; }

            /* Items */
            table.items         { font-size: 7pt; }
            table.items thead th {
                background: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 6.5pt;
                padding: 1mm;
                border-bottom: 1px dashed #000;
            }
            table.items tbody td        { padding: 1mm; border-bottom: none; font-size: 7pt; }
            table.items tbody tr:last-child td { border-bottom: 1px dashed #000; }
            .prod-code          { font-size: 6pt; }

            /* Totales y pagos */
            .bottom-section     { display: block; border-top: 1px dashed #000; padding-top: 2mm; margin-top: 2mm; gap: 0; }
            .pagos-block        { width: 100%; }
            .pagos-block .pago-row  { font-size: 7.5pt; padding: 0.5mm 0; }
            .pagos-block > div:first-child { font-size: 6.5pt; margin-bottom: 1mm; }
            .totales-block      { width: 100%; min-width: unset; margin-top: 2mm; border-top: 1px dashed #000; padding-top: 2mm; }
            .totales-block td   { font-size: 7.5pt; padding: 0.5mm 2mm; }
            .totales-block tr.total-final td { font-size: 9pt; border-top: 1px dashed #000; padding-top: 2mm; }

            /* Footer */
            .ticket-footer      { display: block; text-align: center; font-size: 6.5pt; padding: 2mm; }
            .ticket-footer span { display: block; }
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">

    <!-- Header -->
    <div class="ticket-header">
        <div>
            <h1>
                Ticket de venta
                <?php if ($cancelada): ?>
                    <span class="badge-cancelada">CANCELADA</span>
                <?php endif; ?>
            </h1>
            <div class="folio">Folio <?= htmlspecialchars($venta['folio']) ?></div>
        </div>
        <img src="<?= $app_url ?>/views/assets/img/logo.png"
             alt="Logo"
             style="height:52px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
    </div>

    <!-- Meta -->
    <div class="ticket-meta">
        <div class="meta-block">
            <div class="meta-label">Sucursal</div>
            <div class="meta-value"><?= htmlspecialchars($sucursal) ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Cajero</div>
            <div class="meta-value"><?= htmlspecialchars($cajero) ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Turno</div>
            <div class="meta-value"><?= $venta['id_turno'] ? '#' . str_pad($venta['id_turno'], 4, '0', STR_PAD_LEFT) : '—' ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Fecha</div>
            <div class="meta-value"><?= $fecha_fmt ?></div>
        </div>
    </div>

    <div class="ticket-body">

        <!-- Productos -->
        <div class="section-title">Productos</div>
        <table class="items">
            <thead>
                <tr>
                    <th width="28">#</th>
                    <th>Producto</th>
                    <th width="80">Tipo</th>
                    <th width="90">Cantidad</th>
                    <th width="110" class="text-end">Precio unit.</th>
                    <th width="110" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($venta['items'] as $i => $item): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td>
                        <span style="font-weight:600;"><?= htmlspecialchars($item['producto']) ?></span>
                        <?php if (!empty($item['codigo'])): ?>
                            <div class="prod-code"><?= htmlspecialchars($item['codigo']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['tipo_precio'] === 'unidad'): ?>
                            <span style="font-size:.78rem;background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;">Por unidad</span>
                        <?php else: ?>
                            <span style="font-size:.78rem;background:#f0fdf4;color:#15803d;padding:2px 6px;border-radius:4px;">Presentación</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((float)$item['cantidad'], 2) ?> <?= htmlspecialchars($item['unidad'] ?? '') ?></td>
                    <td class="text-end">$<?= number_format((float)$item['precio_unitario'], 2) ?></td>
                    <td class="text-end" style="font-weight:600;">$<?= number_format((float)$item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totales y formas de pago -->
        <div class="bottom-section">

            <div class="pagos-block">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;font-weight:700;margin-bottom:8px;">Formas de pago</div>
                <?php foreach ($venta['pagos'] as $pago): ?>
                    <?php if ((float)$pago['monto'] <= 0) continue; ?>
                    <div class="pago-row">
                        <span class="pr-label"><?= $labels[$pago['forma_pago']] ?? ucfirst($pago['forma_pago']) ?></span>
                        <span class="pr-value">
                            <?php if ($pago['forma_pago'] === 'efectivo_dolares'): ?>
                                $<?= number_format((float)$pago['monto'], 2) ?> USD
                                <?php if ((float)$pago['monto_pesos'] > 0): ?>
                                    <span style="color:#6c757d;font-weight:400;font-size:.8rem;">(= $<?= number_format((float)$pago['monto_pesos'], 2) ?> MXN)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                $<?= number_format((float)$pago['monto'], 2) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php
                // Cambio
                $total_mxn = 0;
                foreach ($venta['pagos'] as $p) {
                    $total_mxn += $p['forma_pago'] === 'efectivo_dolares'
                        ? (float)$p['monto_pesos']
                        : (float)$p['monto'];
                }
                $cambio    = $total_mxn - (float)$venta['total'];
                $tc        = (float)($venta['tipo_cambio'] ?? 0);
                if ($cambio > 0.005):
                ?>
                <div class="pago-row" style="margin-top:4px;border-top:1px solid #dee2e6;padding-top:6px;">
                    <span class="pr-label" style="font-weight:700;">Cambio</span>
                    <span class="pr-value" style="color:#1b8ea3;">
                        $<?= number_format($cambio, 2) ?> MXN
                        <?php if ($tc > 0): ?>
                            <span style="color:#6c757d;font-weight:400;font-size:.8rem;">(≈ $<?= number_format($cambio / $tc, 2) ?> USD)</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <div class="totales-block">
                <table>
                    <tr>
                        <td class="text-muted">Subtotal</td>
                        <td>$<?= number_format((float)$venta['subtotal'], 2) ?></td>
                    </tr>
                    <?php if ((float)$venta['tipo_cambio'] > 0): ?>
                    <tr>
                        <td class="text-muted">Tipo de cambio</td>
                        <td>$<?= number_format((float)$venta['tipo_cambio'], 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-final">
                        <td>Total</td>
                        <td>$<?= number_format((float)$venta['total'], 2) ?></td>
                    </tr>
                </table>
            </div>

        </div>

    </div>

    <!-- Footer -->
    <div class="ticket-footer">
        <span>Folio <strong><?= htmlspecialchars($venta['folio']) ?></strong> &nbsp;|&nbsp; <?= htmlspecialchars($sucursal) ?></span>
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
        Imprimir ticket
    </button>
    <?php if (!$cancelada && puedo('ventas/pos', 'eliminar')): ?>
    <button class="btn-cancelar" id="btn_cancelar_venta">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        Cancelar venta
    </button>
    <?php endif; ?>
</div>

<?php if (!$cancelada && puedo('ventas/pos', 'eliminar')): ?>
<script src="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script>
document.getElementById('btn_cancelar_venta').addEventListener('click', function () {
    Swal.fire({
        title: '¿Cancelar venta?',
        text: 'Se revertirá el stock de todos los productos. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#64748b',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var btn = document.getElementById('btn_cancelar_venta');
        btn.disabled = true;
        btn.textContent = 'Cancelando...';

        var formData = new FormData();
        formData.append('action', 'cancelar');
        formData.append('id', '<?= $id ?>');

        fetch('<?= $app_url ?>/views/ajax/ajax_ventas_pos.php', {
            method: 'POST',
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.status === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: 'Venta cancelada',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                }).then(function () {
                    location.reload();
                });
            } else {
                btn.disabled = false;
                btn.textContent = 'Cancelar venta';
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, confirmButtonColor: '#4f46e5' });
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Cancelar venta';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonColor: '#4f46e5' });
        });
    });
});
</script>
<?php endif; ?>

</body>
</html>
