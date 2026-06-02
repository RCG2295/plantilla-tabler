<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_inventario_movimientos.php';
require_once __DIR__ . '/../../controllers/controller_inventario_movimientos.php';

// Also needed for registering movements from this view
require_once __DIR__ . '/../../models/model_inventario_productos.php';
require_once __DIR__ . '/../../controllers/controller_inventario_productos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new InventarioMovimientosController();

switch ($action) {

    case 'list':
        if (!puedo('inventario/movimientos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $filtros = [
            'id_producto' => $_GET['id_producto'] ?? '',
            'tipo'        => $_GET['tipo'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
        ];
        $controller->list($filtros);
        break;

    case 'productos':
        if (!puedo('inventario/movimientos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->productos();
        break;

    case 'movimiento':
        if (!puedo('inventario/movimientos', 'ver') || !puedo('inventario/productos', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $prod_controller = new InventarioProductosController();
        $prod_controller->registrarMovimiento($_POST);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
