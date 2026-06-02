<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_ventas_caja.php';
require_once __DIR__ . '/../../models/model_ventas_historial_turnos.php';
require_once __DIR__ . '/../../controllers/controller_ventas_historial_turnos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new VentasHistorialTurnosController();

switch ($action) {

    case 'list':
        if (!puedo('ventas/historial_turnos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->list();
        break;

    case 'corte':
        if (!puedo('ventas/historial_turnos', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getCorte((int) ($_GET['id_turno'] ?? 0));
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
