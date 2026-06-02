<?php

use Phinx\Seed\AbstractSeed;

class InventarioMotivosMovimientoSeed extends AbstractSeed
{
    public function run(): void
    {
        $this->table('inventario_motivos_movimiento')->insert([
            ['nombre' => 'Compra',              'tipo' => 'entrada', 'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Devolución recibida', 'tipo' => 'entrada', 'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Ajuste de inventario','tipo' => 'ambos',   'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Venta',               'tipo' => 'salida',  'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Merma',               'tipo' => 'salida',  'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Devolución enviada',  'tipo' => 'salida',  'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Transferencia',       'tipo' => 'ambos',   'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
        ])->saveData();
    }
}
