<?php

class VentasHistorialVentasController
{
    private VentasHistorialVentasModel $model;

    public function __construct()
    {
        $this->model = new VentasHistorialVentasModel();
    }

    public function list(): void
    {
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        if (!$desde || !$hasta) {
            echo json_encode(['status' => 'ok', 'data' => []]);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido.']);
            return;
        }

        $estado = isset($_GET['estado']) && $_GET['estado'] === '1' ? 1 : 0;
        echo json_encode(['status' => 'ok', 'data' => $this->model->getByRango($desde, $hasta, $estado)]);
    }
}
