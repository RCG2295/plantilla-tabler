<?php

use Phinx\Seed\AbstractSeed;

class InventarioUnidadesMedidaSeed extends AbstractSeed
{
    public function run(): void
    {
        $this->table('inventario_unidades_medida')->insert([
            ['nombre' => 'Pieza',     'abreviatura' => 'pza',  'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg',   'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Gramo',     'abreviatura' => 'g',    'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Litro',     'abreviatura' => 'lt',   'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Mililitro', 'abreviatura' => 'ml',   'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Caja',      'abreviatura' => 'caja', 'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Paquete',   'abreviatura' => 'paq',  'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
            ['nombre' => 'Metro',     'abreviatura' => 'm',    'estado' => 0, 'id_alta' => null, 'fecha_alta' => date('Y-m-d H:i:s')],
        ])->saveData();
    }
}
