<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo '<p>No autorizado.</p>';
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    echo '<p>ID de egreso no válido.</p>';
    exit;
}

$db   = Connection::connect();
$stmt = $db->prepare("
    SELECT e.*,
           c.nombre  AS categoria_nombre,
           s.nombre  AS subcategoria_nombre,
           TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellidos, ''))) AS registrado_por,
           cp.folio  AS compra_folio
    FROM egresos e
    LEFT JOIN egresos_categorias c  ON e.id_categoria    = c.id
    LEFT JOIN egresos_categorias s  ON e.id_subcategoria = s.id
    LEFT JOIN admin_usuarios     u  ON e.id_alta          = u.id
    LEFT JOIN compras           cp  ON e.id_compra        = cp.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$egreso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$egreso) {
    echo '<p>Egreso no encontrado.</p>';
    exit;
}

$app_url       = rtrim($_ENV['APP_URL'] ?? '', '/');
$fecha_fmt     = $egreso['fecha_egreso'] ? date('d/m/Y', strtotime($egreso['fecha_egreso'])) : '—';
$registrado_en = date('d/m/Y H:i', strtotime($egreso['fecha_alta']));

$metodo_map = [
    'efectivo'      => 'Efectivo',
    'transferencia' => 'Transferencia',
    'tarjeta'       => 'Tarjeta',
];
$metodo_txt = $metodo_map[$egreso['metodo_pago']] ?? $egreso['metodo_pago'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket de Egreso — #<?= $egreso['id'] ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #f5f7fa;
        }

        .ticket-wrapper {
            max-width: 640px;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .ticket-header h1 { font-size: 1.4rem; font-weight: 700; letter-spacing: .5px; }
        .ticket-header .sub { font-size: 0.9rem; opacity: .85; margin-top: 4px; }

        .estado-cancelado {
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

        .ticket-meta {
            display: flex;
            flex-wrap: wrap;
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

        .ticket-body { padding: 24px 32px; }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.88rem;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #6c757d; }
        .detail-value { font-weight: 600; text-align: right; }

        .monto-section {
            margin-top: 20px;
            padding: 16px 20px;
            background: #f0f9fb;
            border-radius: 8px;
            border: 1px solid rgba(27,142,163,.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .monto-label { font-size: 0.85rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; }
        .monto-value { font-size: 1.6rem; font-weight: 700; color: #1b8ea3; }

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

    <div class="ticket-header">
        <div>
            <h1>
                Ticket de Egreso
                <?php if ((int) $egreso['estado'] === 1): ?>
                    <span class="estado-cancelado">CANCELADO</span>
                <?php endif; ?>
            </h1>
            <div class="sub">Egreso #<?= $egreso['id'] ?></div>
        </div>
        <img src="<?= $app_url ?>/views/assets/img/logo.png"
             alt="Logo"
             style="height:56px; width:auto; object-fit:contain; filter:brightness(0) invert(1);">
    </div>

    <div class="ticket-meta">
        <div class="meta-block">
            <div class="meta-label">Fecha</div>
            <div class="meta-value"><?= $fecha_fmt ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Método de pago</div>
            <div class="meta-value"><?= htmlspecialchars($metodo_txt) ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-label">Registrado por</div>
            <div class="meta-value">
                <?= htmlspecialchars(trim($egreso['registrado_por']) ?: 'Sistema') ?>
                <br><span style="font-weight:400;font-size:0.8rem;color:#6c757d;"><?= $registrado_en ?></span>
            </div>
        </div>
    </div>

    <div class="ticket-body">

        <div class="detail-row">
            <span class="detail-label">Concepto</span>
            <span class="detail-value"><?= htmlspecialchars($egreso['concepto']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Categoría</span>
            <span class="detail-value">
                <?php if ($egreso['categoria_nombre']): ?>
                    <?= htmlspecialchars($egreso['categoria_nombre']) ?>
                    <?php if ($egreso['subcategoria_nombre']): ?>
                        <span style="font-weight:400;color:#6c757d;"> / <?= htmlspecialchars($egreso['subcategoria_nombre']) ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#6c757d;font-weight:400;">Sin categoría</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if ($egreso['referencia']): ?>
        <div class="detail-row">
            <span class="detail-label">Referencia</span>
            <span class="detail-value"><?= htmlspecialchars($egreso['referencia']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($egreso['compra_folio']): ?>
        <div class="detail-row">
            <span class="detail-label">Asociado a compra</span>
            <span class="detail-value"><code><?= htmlspecialchars($egreso['compra_folio']) ?></code></span>
        </div>
        <?php endif; ?>
        <?php if ($egreso['notas']): ?>
        <div class="detail-row">
            <span class="detail-label">Notas</span>
            <span class="detail-value" style="max-width:60%;font-weight:400;"><?= htmlspecialchars($egreso['notas']) ?></span>
        </div>
        <?php endif; ?>

        <div class="monto-section">
            <span class="monto-label">Monto total</span>
            <span class="monto-value">$<?= number_format((float) $egreso['monto'], 2) ?></span>
        </div>

    </div>

    <div class="ticket-footer">
        <span>Egreso #<?= $egreso['id'] ?></span>
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
