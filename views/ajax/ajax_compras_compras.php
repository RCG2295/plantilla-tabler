<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_compras_compras.php';
require_once __DIR__ . '/../../controllers/controller_compras_compras.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new ComprasComprasController();

switch ($action) {

    case 'list':
        if (!puedo('compras/compras', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'list_activas':
        if (!puedo('compras/compras', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->listActivas();
        break;

    case 'list_canceladas':
        if (!puedo('compras/compras', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->listCanceladas();
        break;

    case 'get':
        if (!puedo('compras/compras', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['id'] ?? 0);
        break;

    case 'save':
        if (!puedo('compras/compras', 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST);
        break;

    case 'cancelar':
        if (!puedo('compras/compras', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->cancelar($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
