<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_admin_sucursales.php';
require_once __DIR__ . '/../../controllers/controller_admin_sucursales.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new AdminSucursalesController();

switch ($action) {

    case 'list':
        if (!puedo('admin/sucursales', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'for_select':
        // Solo superadmin — usado en navbar dropdown
        if (empty($_SESSION['es_superadmin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'list_select':
        // Cualquier usuario autenticado — para selects en formularios
        $controller->list();
        break;

    case 'get':
        if (!puedo('admin/sucursales', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get((int) ($_GET['id'] ?? 0));
        break;

    case 'save':
        $is_edit = isset($_POST['id']) && (int) $_POST['id'] > 0;
        if (!puedo('admin/sucursales', $is_edit ? 'editar' : 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST);
        break;

    case 'delete':
        if (!puedo('admin/sucursales', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->delete((int) ($_POST['id'] ?? 0));
        break;

    case 'switch':
        $controller->switchSucursal((int) ($_POST['id'] ?? 0));
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
