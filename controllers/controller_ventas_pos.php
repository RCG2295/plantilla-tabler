<?php

class VentasPosController
{
    private VentasPosModel  $model;
    private VentasCajaModel $cajaModel;

    public function __construct()
    {
        $this->model     = new VentasPosModel();
        $this->cajaModel = new VentasCajaModel();
    }

    public function getProductos(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getProductos()]);
    }

    public function getCategorias(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getCategorias()]);
    }

    public function getTipoCambio(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getTipoCambio()]);
    }

    public function getStockActual(int $id): void
    {
        echo json_encode(['status' => 'ok', 'stock' => $this->model->getStockActual($id)]);
    }

    public function getTicket(int $id): void
    {
        $data = $this->model->getVentaParaTicket($id);
        if (!$data) {
            echo json_encode(['status' => 'error', 'message' => 'Venta no encontrada.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $data]);
    }

    public function registrarVenta(array $post): void
    {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        $turno = $this->cajaModel->getTurnoActivo($id_usuario);
        if (!$turno) {
            echo json_encode(['status' => 'error', 'message' => 'No tienes un turno activo. Abre la caja primero.']);
            return;
        }

        $items = json_decode($post['items'] ?? '[]', true);
        $pagos = json_decode($post['pagos'] ?? '[]', true);

        if (empty($items)) {
            echo json_encode(['status' => 'error', 'message' => 'El carrito está vacío.']);
            return;
        }

        $venta = [
            'id_turno'    => (int) $turno['id'],
            'subtotal'    => (float) ($post['subtotal'] ?? 0),
            'total'       => (float) ($post['total']    ?? 0),
            'tipo_cambio' => (float) ($post['tipo_cambio'] ?? 0),
        ];

        $result = $this->model->registrarVenta($venta, $items, $pagos);

        if ($result['ok']) {
            echo json_encode(['status' => 'ok', 'id_venta' => $result['id_venta'], 'folio' => $result['folio']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
        }
    }

    public function cancelarVenta(int $id): void
    {
        $result = $this->model->cancelarVenta($id);
        if ($result['ok']) {
            echo json_encode(['status' => 'ok', 'message' => 'Venta cancelada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
        }
    }
}
