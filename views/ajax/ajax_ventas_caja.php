<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../models/model_ventas_tipo_cambio.php';
require_once __DIR__ . '/../../models/model_ventas_caja.php';
require_once __DIR__ . '/../../models/model_ventas_pos.php';
require_once __DIR__ . '/../../controllers/controller_ventas_tipo_cambio.php';
require_once __DIR__ . '/../../controllers/controller_ventas_caja.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new VentasCajaController();

switch ($action) {

    case 'turno_activo':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getTurnoActivo();
        break;

    case 'list':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getAll();
        break;

    case 'denominaciones_fijas':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getDenominacionesFijas();
        break;

    case 'resumen':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getResumen((int) ($_GET['id_turno'] ?? 0));
        break;

    case 'corte':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getCorte((int) ($_GET['id_turno'] ?? 0));
        break;

    case 'denominaciones':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $momento = in_array($_GET['momento'] ?? '', ['apertura', 'cierre']) ? $_GET['momento'] : 'apertura';
        $ctrl->getDenominaciones((int) ($_GET['id_turno'] ?? 0), $momento);
        break;

    case 'movimientos':
        if (!puedo('ventas/caja', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->getMovimientos((int) ($_GET['id_turno'] ?? 0));
        break;

    case 'iniciar':
        if (!puedo('ventas/caja', 'crear')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $post = $_POST;
        if (isset($post['denominaciones']) && is_string($post['denominaciones'])) {
            $post['denominaciones'] = json_decode($post['denominaciones'], true) ?: [];
        }
        $ctrl->iniciar($post);
        break;

    case 'cerrar':
        if (!puedo('ventas/caja', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $post = $_POST;
        if (isset($post['denominaciones']) && is_string($post['denominaciones'])) {
            $post['denominaciones'] = json_decode($post['denominaciones'], true) ?: [];
        }
        $ctrl->cerrar($post);
        break;

    case 'movimiento':
        if (!puedo('ventas/caja', 'editar')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $ctrl->insertMovimiento($_POST);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
