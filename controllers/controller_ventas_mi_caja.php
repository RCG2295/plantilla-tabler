<?php

class VentasMiCajaController
{
    private VentasCajaModel $cajaMod;
    private VentasPosModel  $posMod;

    public function __construct()
    {
        $this->cajaMod = new VentasCajaModel();
        $this->posMod  = new VentasPosModel();
    }

    public function getTurnoActivo(): void
    {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        $turno = $this->cajaMod->getTurnoActivo($id_usuario);
        echo json_encode(['status' => 'ok', 'data' => $turno]);
    }

    public function getResumen(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->cajaMod->getResumenTurno($id_turno)]);
    }

    public function getVentas(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->posMod->getVentasByTurno($id_turno)]);
    }

    public function getMovimientos(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->cajaMod->getMovimientos($id_turno)]);
    }

    public function insertMovimiento(array $post): void
    {
        $ctrl = new VentasCajaController();
        $ctrl->insertMovimiento($post);
    }
}
