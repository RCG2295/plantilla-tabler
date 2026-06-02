<?php

class InventarioMovimientosController
{
    public function list($filtros)
    {
        $model = new InventarioMovimientosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll($filtros)]);
    }

    public function productos()
    {
        $model = new InventarioMovimientosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getProductosActivos()]);
    }
}
