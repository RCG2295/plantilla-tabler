<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_compras_compras.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo '<p>No autorizado.</p>';
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    echo '<p>ID de compra no válido.</p>';
    exit;
}

$model  = new ComprasComprasModel();
$compra = $model->getById($id);

if (!$compra) {
    echo '<p>Compra no encontrada.</p>';
    exit;
}

$detalle   = $model->getDetalle($id);
$app_url   = rtrim($_ENV['APP_URL'] ?? '', '/');
$fecha_fmt = date('d/m/Y', strtotime($compra['fecha_compra']));
$registrado_en = date('d/m/Y H:i', strtotime($compra['fecha_alta']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket de Compra — <?= htmlspecialchars($compra['folio']) ?></title>
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

        .ticket-header {
            background: #1b8ea3;
            color: #fff;
            padding: 24px 32px 18px;
        }
        .ticket-header h1 { font-size: 1.4rem; font-weight: 700; letter-spacing: .5px; }
        .ticket-header .folio { font-size: 1rem; opacity: .85; margin-top: 4px; }

        .ticket-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            border-bottom: 1px solid #e8eaf0;
        }
        .meta-block {
            flex: 1 1 33%;
            padding: 14px 32px;
            border-right: 1px solid #e8eaf0;
        }
        .meta-block:last-child { border-right: none; }
        .meta-label { font-size: 0.72rem; text-transform: uppercase; color: #6c757d; letter-spacing: .5px; margin-bottom: 3px; }
        .meta-value { font-weight: 600; font-size: 0.9rem; }

        .ticket-body { padding: 20px 32px; }

        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.detalle thead th {
            background: #f0f4f8;
            padding: 8px 10px;
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: .4px;
            border-bottom: 2px solid #dee2e6;
        }
        table.detalle thead th.text-end { text-align: right; }
        table.detalle tbody td {
            padding: 9px 10px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        table.detalle tbody td.text-end { text-align: right; }
        table.detalle tbody tr:last-child td { border-bottom: none; }
        table.detalle tbody tr:hover td { background: #fafbfc; }

        .qty-hint { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }

        .totales-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
            border-top: 2px solid #e8eaf0;
            padding-top: 12px;
        }
        .totales-table { min-width: 240px; }
        .totales-table tr td { padding: 3px 8px; font-size: 0.88rem; }
        .totales-table tr td:last-child { text-align: right; font-weight: 600; }
        .totales-table tr.total-final td { font-size: 1.05rem; font-weight: 700; color: #1b8ea3; border-top: 1px solid #dee2e6; padding-top: 8px; }

        .ticket-footer {
            padding: 14px 32px;
            background: #f8f9fa;
            border-top: 1px solid #e8eaf0;
            font-size: 0.78rem;
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

        .estado-cancelada {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 10px;
            vertical-align: middle;
        }

        @media print {
            body { background: #fff; }
            .ticket-wrapper { box-shadow: none; margin: 0; border-radius: 0; max-width: 100%; }
            .btn-print { display: none !important; }
            .ticket-footer { border-top: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">

    <!-- Header -->
    <div class="ticket-header" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
        <div>
            <h1>
                Ticket de Compra
                <?php if ((int) $compra['estado'] === 1): ?>
                    <span class="estado-cancelada">CANCELADA</span>
                <?php endif; ?>
            </h1>
            <div class="folio"><?= htmlspecialchars($compra['folio']) ?></div>
        </div>
        <img src="<?= $app_url ?>/views/assets/img/logo.png"
             alt="Logo"
             style="height:56px; width:auto; object-fit:contain; filter:brightness(0) invert(1);">
    </div>

    <!-- Meta info -->
    <div class="ticket-meta">
        <div class="meta-block">
            <div class="meta-label">Fecha de compra</div>
            <div class="meta-value"><?= $fecha_fmt ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Proveedor</div>
            <div class="meta-value">
                <?php if ($compra['proveedor_nombre']): ?>
                    <?= htmlspecialchars($compra['proveedor_nombre']) ?>
                    <?php if ($compra['proveedor_rfc']): ?>
                        <br><span style="font-weight:400;font-size:0.8rem;color:#6c757d;">RFC: <?= htmlspecialchars($compra['proveedor_rfc']) ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#6c757d;font-weight:400;">Sin proveedor</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Registrado por</div>
            <div class="meta-value">
                <?= htmlspecialchars(trim(($compra['usuario_nombre'] ?? '') . ' ' . ($compra['usuario_apellidos'] ?? ''))) ?: 'Sistema' ?>
                <br><span style="font-weight:400;font-size:0.8rem;color:#6c757d;"><?= $registrado_en ?></span>
            </div>
        </div>
    </div>

    <!-- Detalle -->
    <div class="ticket-body">
        <?php if ($compra['notas']): ?>
            <p style="font-size:0.85rem;color:#6c757d;margin-bottom:12px;">
                <strong>Notas:</strong> <?= htmlspecialchars($compra['notas']) ?>
            </p>
        <?php endif; ?>

        <table class="detalle">
            <thead>
                <tr>
                    <th width="32">#</th>
                    <th>Producto</th>
                    <th width="110">Cantidad</th>
                    <th width="110" class="text-end">Precio unit.</th>
                    <th width="70" class="text-end">IVA %</th>
                    <th width="110" class="text-end">IVA</th>
                    <th width="120" class="text-end">Total línea</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $i => $d): ?>
                <?php
                    $seFragc   = (int) $d['se_fracciona'];
                    $cantPres  = (float) $d['cantidad_presentacion'];
                    $ab        = $d['abreviatura'] ?? 'pza';
                ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td>
                        <span class="fw-semibold"><?= htmlspecialchars($d['producto_nombre']) ?></span>
                        <br><code style="font-size:0.75rem;"><?= htmlspecialchars($d['producto_codigo']) ?></code>
                    </td>
                    <td>
                        <?php if ($seFragc): ?>
                            <?= number_format((float)$d['cantidad'], 0) ?> pres.
                            <div class="qty-hint"><?= number_format((float)$d['cantidad_base'], 2) ?> <?= htmlspecialchars($ab) ?></div>
                        <?php else: ?>
                            <?= number_format((float)$d['cantidad'], 2) ?> <?= htmlspecialchars($ab) ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">$<?= number_format((float)$d['precio_unitario'], 2) ?></td>
                    <td class="text-end"><?= number_format((float)$d['iva'], 2) ?>%</td>
                    <td class="text-end">$<?= number_format((float)$d['iva_monto'], 2) ?></td>
                    <td class="text-end fw-semibold">$<?= number_format((float)$d['total_linea'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totales-section">
            <table class="totales-table">
                <tr>
                    <td>Subtotal</td>
                    <td>$<?= number_format((float)$compra['subtotal'], 2) ?></td>
                </tr>
                <tr>
                    <td>IVA</td>
                    <td>$<?= number_format((float)$compra['iva_total'], 2) ?></td>
                </tr>
                <tr class="total-final">
                    <td>Total</td>
                    <td>$<?= number_format((float)$compra['total'], 2) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="ticket-footer">
        <span>Folio: <strong><?= htmlspecialchars($compra['folio']) ?></strong></span>
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
</div>

</body>
</html>
