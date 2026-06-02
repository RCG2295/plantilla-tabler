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
require_once __DIR__ . '/../../models/model_ventas_pos.php';
require_once __DIR__ . '/../../controllers/controller_ventas_tipo_cambio.php';
require_once __DIR__ . '/../../controllers/controller_ventas_caja.php';
require_once __DIR__ . '/../../controllers/controller_ventas_pos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new VentasPosController();

switch ($action) {

    case 'productos':
        if (!puedo('ventas/pos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getProductos();
        break;

    case 'categorias':
        if (!puedo('ventas/pos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getCategorias();
        break;

    case 'tipo_cambio':
        if (!puedo('ventas/pos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getTipoCambio();
        break;

    case 'stock':
        if (!puedo('ventas/pos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getStockActual((int) ($_GET['id'] ?? 0));
        break;

    case 'ticket':
        if (!puedo('ventas/pos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getTicket((int) ($_GET['id'] ?? 0));
        break;

    case 'registrar':
        if (!puedo('ventas/pos', 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->registrarVenta($_POST);
        break;

    case 'cancelar':
        if (!puedo('ventas/pos', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->cancelarVenta((int) ($_POST['id'] ?? 0));
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
