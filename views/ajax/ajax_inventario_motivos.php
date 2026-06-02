<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_inventario_motivos.php';
require_once __DIR__ . '/../../controllers/controller_inventario_motivos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new InventarioMotivosController();

switch ($action) {

    case 'list':
        if (!puedo('inventario/motivos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'list_by_tipo':
        // Used internally from other modules (movimientos)
        if (!isset($_SESSION['usuario_id'])) { echo json_encode(['status' => 'error']); exit; }
        $controller->listByTipo($_GET['tipo'] ?? '');
        break;

    case 'get':
        if (!puedo('inventario/motivos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['id'] ?? 0);
        break;

    case 'save':
        $is_edit = isset($_POST['id']) && (int) $_POST['id'] > 0;
        if (!puedo('inventario/motivos', $is_edit ? 'editar' : 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST);
        break;

    case 'delete':
        if (!puedo('inventario/motivos', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->delete($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
