<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_ventas_tipo_cambio.php';
require_once __DIR__ . '/../../controllers/controller_ventas_tipo_cambio.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new VentasTipoCambioController();

switch ($action) {

    case 'list':
        if (!puedo('ventas/tipo_cambio', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->list();
        break;

    case 'vigente':
        if (!puedo('ventas/tipo_cambio', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getVigente();
        break;

    case 'save':
        if (!puedo('ventas/tipo_cambio', 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->save($_POST);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
