<?php

class VentasTipoCambioController
{
    private VentasTipoCambioModel $model;

    public function __construct()
    {
        $this->model = new VentasTipoCambioModel();
    }

    public function list(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getAll()]);
    }

    public function getVigente(): void
    {
        $row = $this->model->getVigente();
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save(array $post): void
    {
        $valor = (float) ($post['valor'] ?? 0);
        if ($valor <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'El valor debe ser mayor a 0.']);
            return;
        }
        $this->model->insert(['valor' => $valor, 'id_alta' => $_SESSION['usuario_id']]);
        echo json_encode(['status' => 'ok', 'message' => 'Tipo de cambio registrado correctamente.']);
    }
}
