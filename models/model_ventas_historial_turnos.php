<?php

class VentasHistorialTurnosModel
{
    private VentasCajaModel $caja;

    public function __construct()
    {
        $this->caja = new VentasCajaModel();
    }

    public function getByRango(string $desde, string $hasta): array
    {
        return $this->caja->getByRango($desde, $hasta);
    }

    public function getCorte(int $id_turno)
    {
        return $this->caja->getCorte($id_turno);
    }
}
