<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_cfg_roles.php';
require_once __DIR__ . '/../../controllers/controller_cfg_roles.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new CfgRolesController();

switch ($action) {

    case 'list':
        if (!puedo('cfg/roles', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    // Used by other modules (admin_usuarios, etc.) to populate role selects
    case 'select':
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
            exit;
        }
        $controller->select();
        break;

    case 'get':
        if (!puedo('cfg/roles', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['id'] ?? 0);
        break;

    case 'save':
        $id = isset($_POST['id']) && (int)$_POST['id'] > 0;
        if (!puedo('cfg/roles', $id ? 'editar' : 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST);
        break;

    case 'delete':
        if (!puedo('cfg/roles', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->delete($_POST['id'] ?? 0);
        break;

    case 'get_permisos':
        if (!puedo('cfg/roles', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->getPermisos($_GET['id_rol'] ?? 0);
        break;

    case 'save_permisos':
        if (!puedo('cfg/roles', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->savePermisos($_POST);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
