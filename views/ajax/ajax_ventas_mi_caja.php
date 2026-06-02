<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_ventas_tipo_cambio.php';
require_once __DIR__ . '/../../models/model_ventas_caja.php';
require_once __DIR__ . '/../../models/model_ventas_mi_caja.php';
require_once __DIR__ . '/../../models/model_ventas_pos.php';
require_once __DIR__ . '/../../controllers/controller_ventas_tipo_cambio.php';
require_once __DIR__ . '/../../controllers/controller_ventas_caja.php';
require_once __DIR__ . '/../../controllers/controller_ventas_mi_caja.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new VentasMiCajaController();

switch ($action) {

    case 'turno_activo':
        if (!puedo('ventas/mi_caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getTurnoActivo();
        break;

    case 'resumen':
        if (!puedo('ventas/mi_caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getResumen((int) ($_GET['id_turno'] ?? 0));
        break;

    case 'ventas':
        if (!puedo('ventas/mi_caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getVentas((int) ($_GET['id_turno'] ?? 0));
        break;

    case 'movimientos':
        if (!puedo('ventas/mi_caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getMovimientos((int) ($_GET['id_turno'] ?? 0));
        break;

    case 'movimiento':
        if (!puedo('ventas/mi_caja', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->insertMovimiento($_POST);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
