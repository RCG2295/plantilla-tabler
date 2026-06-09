<?php

use Phinx\Seed\AbstractSeed;

class InventarioAltaProductoSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['InventarioMotivosMovimientoSeed'];
    }

    public function run(): void
    {
        $this->table('inventario_motivos_movimiento')->insert([
            [
                'nombre'     => 'Alta de producto',
                'tipo'       => 'entrada',
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }
}
