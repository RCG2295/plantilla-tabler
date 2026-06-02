<?php

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../models/model_login.php';
require_once __DIR__ . '/../../controllers/controller_login.php';

header('Content-Type: application/json');

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'Email y contraseña son requeridos.']);
    exit;
}

$controller = new LoginController();
$resultado  = $controller->login($email, $password);

echo json_encode($resultado);
