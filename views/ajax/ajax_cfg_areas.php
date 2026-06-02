<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_cfg_areas.php';
require_once __DIR__ . '/../../controllers/controller_cfg_areas.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new CfgAreasController();

switch ($action) {

    case 'list':
        if (!puedo('cfg/areas', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'get':
        if (!puedo('cfg/areas', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['id'] ?? 0);
        break;

    case 'save':
        $id = isset($_POST['id']) && (int)$_POST['id'] > 0;
        if (!puedo('cfg/areas', $id ? 'editar' : 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST);
        break;

    case 'delete':
        if (!puedo('cfg/areas', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->delete($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
