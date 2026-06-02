<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_inventario_productos.php';
require_once __DIR__ . '/../../controllers/controller_inventario_productos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new InventarioProductosController();

switch ($action) {

    case 'list':
        if (!puedo('inventario/productos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->list();
        break;

    case 'get':
        if (!puedo('inventario/productos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['id'] ?? 0);
        break;

    case 'save':
        $is_edit = isset($_POST['id']) && (int) $_POST['id'] > 0;
        if (!puedo('inventario/productos', $is_edit ? 'editar' : 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->save($_POST, $_FILES);
        break;

    case 'set_foto_principal':
        if (!puedo('inventario/productos', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->setFotoPrincipal($_POST['id_producto'] ?? 0, $_POST['id_foto'] ?? 0);
        break;

    case 'delete_foto':
        if (!puedo('inventario/productos', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->deleteFoto($_POST['id'] ?? 0);
        break;

    case 'movimiento':
        if (!puedo('inventario/productos', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->registrarMovimiento($_POST);
        break;

    case 'movimientos':
        if (!puedo('inventario/productos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->getMovimientos($_GET['id_producto'] ?? 0);
        break;

    case 'delete':
        if (!puedo('inventario/productos', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->delete($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
