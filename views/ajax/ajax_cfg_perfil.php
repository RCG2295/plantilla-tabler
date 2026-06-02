<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_cfg_perfil.php';
require_once __DIR__ . '/../../controllers/controller_cfg_perfil.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$id_sesion  = (int) $_SESSION['usuario_id'];
$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new CfgPerfilController();

switch ($action) {

    case 'get':
        $controller->get($id_sesion);
        break;

    case 'update_info':
        $controller->updateInfo($_POST, $_FILES, $id_sesion);
        break;

    case 'update_password':
        $controller->updatePassword($_POST, $id_sesion);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
