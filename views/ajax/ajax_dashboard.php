<?php

ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_dashboard.php';
require_once __DIR__ . '/../../controllers/controller_dashboard.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_GET['action'] ?? '';
$ctrl   = new DashboardController();

switch ($action) {

    case 'ventas':
        if (!puedo('ventas/historial_ventas', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->getVentas();
        break;

    case 'compras':
        if (!puedo('compras/compras', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->getCompras();
        break;

    case 'egresos':
        if (!puedo('egresos/egresos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->getEgresos();
        break;

    case 'inventario':
        if (!puedo('inventario/productos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->getInventario();
        break;

    case 'turno':
        if (!puedo('ventas/mi_caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->getTurno();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Accion no valida.']);
        break;
}
