<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_egresos_categorias.php';
require_once __DIR__ . '/../../models/model_egresos_egresos.php';
require_once __DIR__ . '/../../controllers/controller_egresos_categorias.php';
require_once __DIR__ . '/../../controllers/controller_egresos_egresos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new EgresosEgresosController();
$ctrlCat = new EgresosCategoriasController();

switch ($action) {

    case 'list_activos':
        if (!puedo('egresos/egresos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->listActivos();
        break;

    case 'list_cancelados':
        if (!puedo('egresos/egresos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->listCancelados();
        break;

    case 'get':
        if (!puedo('egresos/egresos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->get($_GET['id'] ?? 0);
        break;

    case 'padres':
        if (!puedo('egresos/egresos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrlCat->getPadresManual();
        break;

    case 'subcategorias':
        if (!puedo('egresos/egresos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrlCat->getSubcategorias($_GET['id_padre'] ?? 0);
        break;

    case 'save':
        if (!puedo('egresos/egresos', 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->save($_POST);
        break;

    case 'cancelar':
        if (!puedo('egresos/egresos', 'eliminar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $ctrl->cancelar($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
