<?php

use Phinx\Seed\AbstractSeed;

class CfgAreasSeed extends AbstractSeed
{
    public function run(): void
    {
        $this->table('cfg_areas')->insert([
            [
                'nombre'     => 'Dashboard',
                'icono'      => 'ti ti-layout-dashboard',
                'orden'      => 1,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'     => 'Administración',
                'icono'      => 'ti ti-settings',
                'orden'      => 2,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'     => 'Reportes',
                'icono'      => 'ti ti-report-analytics',
                'orden'      => 3,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'     => 'Configuración',
                'icono'      => 'ti ti-adjustments',
                'orden'      => 4,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }
}
