<?php

class VentasHistorialTurnosController
{
    private VentasHistorialTurnosModel $model;

    public function __construct()
    {
        $this->model = new VentasHistorialTurnosModel();
    }

    public function list(): void
    {
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        if (!$desde || !$hasta) {
            echo json_encode(['status' => 'ok', 'data' => []]);
            return;
        }

        // Validate date format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido.']);
            return;
        }

        echo json_encode(['status' => 'ok', 'data' => $this->model->getByRango($desde, $hasta)]);
    }

    public function getCorte(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getCorte($id_turno)]);
    }
}
