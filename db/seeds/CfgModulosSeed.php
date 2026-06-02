<?php

use Phinx\Seed\AbstractSeed;

// Run after CfgAreasSeed (requires area IDs 1-4)
class CfgModulosSeed extends AbstractSeed
{
    public function run(): void
    {
        $this->table('cfg_modulos')->insert([
            // Area 1: Dashboard
            [
                'id_area'    => 1,
                'clave'      => 'dashboard',
                'nombre'     => 'Dashboard',
                'icono'      => null,
                'orden'      => 1,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            // Area 2: Administración
            [
                'id_area'    => 2,
                'clave'      => 'admin/usuarios',
                'nombre'     => 'Usuarios',
                'icono'      => null,
                'orden'      => 1,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            // Area 3: Reportes
            [
                'id_area'    => 3,
                'clave'      => 'reportes/notificaciones',
                'nombre'     => 'Notificaciones',
                'icono'      => null,
                'orden'      => 1,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            // Area 4: Configuración
            [
                'id_area'    => 4,
                'clave'      => 'cfg/areas',
                'nombre'     => 'Áreas',
                'icono'      => null,
                'orden'      => 1,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            [
                'id_area'    => 4,
                'clave'      => 'cfg/modulos',
                'nombre'     => 'Módulos',
                'icono'      => null,
                'orden'      => 2,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
            [
                'id_area'    => 4,
                'clave'      => 'cfg/roles',
                'nombre'     => 'Roles y Permisos',
                'icono'      => null,
                'orden'      => 3,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }
}
