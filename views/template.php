<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';

$app_url     = TemplateController::getUrlController();
$app_version = '1.0.0';

// Detect current route
$base_path    = rtrim(parse_url($app_url, PHP_URL_PATH) ?? '', '/');
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ruta         = trim(str_replace($base_path, '', $request_path), '/');
if ($ruta === '') $ruta = 'dashboard';

$usuario_logueado = isset($_SESSION['usuario_id']);

// Direct action — no HTML
if ($ruta === 'salir') {
    include __DIR__ . '/modules/salir.php';
    exit;
}

// Protected route without session → login
if ($ruta !== 'login' && !$usuario_logueado) {
    header('Location: ' . $app_url . '/login');
    exit;
}

// Already logged in going to login → dashboard
if ($ruta === 'login' && $usuario_logueado) {
    header('Location: ' . $app_url . '/dashboard');
    exit;
}

// ── Login layout ──────────────────────────────────────────────────────────
if ($ruta === 'login'):
    $ruta_modulo = __DIR__ . '/modules/login.php';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="app-url" content="<?= htmlspecialchars($app_url) ?>"/>
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="<?= $app_url ?>/views/assets/css/theme.css?v=<?= $app_version ?>"/>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.css"/>
</head>
<body>
<?php include $ruta_modulo; ?>
<script src="<?= $app_url ?>/node_modules/jquery/dist/jquery.min.js"></script>
<script src="<?= $app_url ?>/node_modules/@tabler/core/dist/js/tabler.min.js"></script>
<script src="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="<?= $app_url ?>/views/assets/js/login.js?v=<?= $app_version ?>"></script>
</body>
</html>
<?php
    exit;
endif;

// ── Authenticated routes ──────────────────────────────────────────────────
// Derive filename from route: admin/usuarios → admin_usuarios
$archivo_modulo = str_replace('/', '_', $ruta);
$ruta_modulo    = __DIR__ . '/modules/' . $archivo_modulo . '.php';

if (!file_exists($ruta_modulo) || !puedo($ruta, 'ver')) {
    $archivo_modulo = '404';
    $ruta_modulo    = __DIR__ . '/modules/404.php';
}

// ── POS layout (full-screen, no sidebar/navbar) ─────────────────────────
if ($ruta === 'ventas/pos' && $archivo_modulo !== '404'):
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="app-url" content="<?= htmlspecialchars($app_url) ?>"/>
    <title>Punto de Venta</title>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css"/>
    <link rel="stylesheet" href="<?= $app_url ?>/views/assets/css/theme.css?v=<?= $app_version ?>"/>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.css"/>
</head>
<body>
<?php include $ruta_modulo; ?>
<script src="<?= $app_url ?>/node_modules/jquery/dist/jquery.min.js"></script>
<script src="<?= $app_url ?>/node_modules/@tabler/core/dist/js/tabler.min.js"></script>
<script>window.bootstrap = window.tabler;</script>
<script src="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="<?= $app_url ?>/views/assets/js/general.js?v=<?= $app_version ?>"></script>
<script src="<?= $app_url ?>/views/assets/js/ventas_pos.js?v=<?= $app_version ?>"></script>
</body>
</html>
<?php
    exit;
endif;

// ── Full layout ───────────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="app-url" content="<?= htmlspecialchars($app_url) ?>"/>
    <title>Sistema</title>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css"/>
    <link rel="stylesheet" href="<?= $app_url ?>/views/assets/css/theme.css?v=<?= $app_version ?>"/>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/flatpickr/dist/flatpickr.min.css"/>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/tom-select/dist/css/tom-select.bootstrap5.min.css"/>
    <link rel="stylesheet" href="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.css"/>
</head>
<body>

<div id="ajax-loader"></div>
<div class="page">
    <?php include __DIR__ . '/modules/sections/sidebar.php'; ?>

    <div class="page-wrapper">
        <?php include __DIR__ . '/modules/sections/navbar.php'; ?>

        <div class="page-body">
            <div class="container-xl">
                <?php include $ruta_modulo; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="<?= $app_url ?>/node_modules/jquery/dist/jquery.min.js"></script>
<script src="<?= $app_url ?>/node_modules/@tabler/core/dist/js/tabler.min.js"></script>
<script>window.bootstrap = window.tabler;</script>
<script src="<?= $app_url ?>/node_modules/datatables.net/js/dataTables.min.js"></script>
<script src="<?= $app_url ?>/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= $app_url ?>/node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="<?= $app_url ?>/node_modules/apexcharts/dist/apexcharts.min.js"></script>
<script src="<?= $app_url ?>/node_modules/flatpickr/dist/flatpickr.min.js"></script>
<script src="<?= $app_url ?>/node_modules/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= $app_url ?>/views/assets/js/general.js?v=<?= $app_version ?>"></script>
<script src="<?= $app_url ?>/views/assets/js/app.js?v=<?= $app_version ?>"></script>
<?php
$js_modulo = __DIR__ . '/assets/js/' . $archivo_modulo . '.js';
if (file_exists($js_modulo)):
?>
<script src="<?= $app_url ?>/views/assets/js/<?= htmlspecialchars($archivo_modulo) ?>.js?v=<?= $app_version ?>"></script>
<?php endif; ?>

</body>
</html>
