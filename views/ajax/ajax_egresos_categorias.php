<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_egresos_categorias.php';
require_once __DIR__ . '/../../controllers/controller_egresos_categorias.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new EgresosCategoriasController();

switch ($action) {

    case 'list':
        if (!puedo('egresos/categorias', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'list_padres':
        if (!puedo('egresos/categorias', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->listPadres();
        break;

    case 'list_subcategorias':
        if (!puedo('egresos/categorias', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->listSubcategorias($_GET['id_padre'] ?? 0);
        break;

    case 'get':
        if (!puedo('egresos/categorias', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['id'] ?? 0);
        break;

    case 'padres':
        if (!puedo('egresos/categorias', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->getPadres();
        break;

    case 'subcategorias':
        if (!puedo('egresos/categorias', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->getSubcategorias($_GET['id_padre'] ?? 0);
        break;

    case 'save':
        $is_edit = isset($_POST['id']) && (int) $_POST['id'] > 0;
        if (!puedo('egresos/categorias', $is_edit ? 'editar' : 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST);
        break;

    case 'delete':
        if (!puedo('egresos/categorias', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->delete($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
