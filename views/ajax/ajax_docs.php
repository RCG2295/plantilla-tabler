<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../controllers/controller_docs.php';

$action     = $_GET['action'] ?? '';
$controller = new DocsController();

// download_pdf streams a binary file — handle before any header is sent
if ($action === 'download_pdf') {
    if (!isset($_SESSION['usuario_id']) || !puedo('docs', 'ver')) {
        http_response_code(403);
        exit('No autorizado.');
    }
    $controller->downloadPdf();
    exit;
}

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    exit;
}

switch ($action) {

    case 'get':
        if (!puedo('docs', 'ver')) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            exit;
        }
        $controller->get($_GET['doc'] ?? 'index');
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
