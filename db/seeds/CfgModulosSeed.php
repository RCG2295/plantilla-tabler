<?php

use Phinx\Seed\AbstractSeed;

class CfgModulosSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['CfgAreasSeed'];
    }

    public function run(): void
    {
        $rows = $this->fetchAll("SELECT id, nombre FROM cfg_areas WHERE estado != 2");
        $area = [];
        foreach ($rows as $r) {
            $area[$r['nombre']] = (int) $r['id'];
        }

        $now = date('Y-m-d H:i:s');

        $modulos = [
            // Dashboard
            ['id_area' => $area['Dashboard']      ?? 0, 'clave' => 'dashboard',               'nombre' => 'Dashboard',        'icono' => null, 'orden' => 1],
            // Administración
            ['id_area' => $area['Administración'] ?? 0, 'clave' => 'admin/usuarios',           'nombre' => 'Usuarios',         'icono' => null, 'orden' => 1],
            // Reportes
            ['id_area' => $area['Reportes']       ?? 0, 'clave' => 'reportes/notificaciones',  'nombre' => 'Notificaciones',   'icono' => null, 'orden' => 1],
            // Configuración
            ['id_area' => $area['Configuración']  ?? 0, 'clave' => 'cfg/areas',                'nombre' => 'Áreas',            'icono' => null, 'orden' => 1],
            ['id_area' => $area['Configuración']  ?? 0, 'clave' => 'cfg/modulos',              'nombre' => 'Módulos',          'icono' => null, 'orden' => 2],
            ['id_area' => $area['Configuración']  ?? 0, 'clave' => 'cfg/roles',                'nombre' => 'Roles y Permisos', 'icono' => null, 'orden' => 3],
        ];

        $insert = [];
        foreach ($modulos as $m) {
            if ($m['id_area'] <= 0) continue;
            $insert[] = [
                'id_area'    => $m['id_area'],
                'clave'      => $m['clave'],
                'nombre'     => $m['nombre'],
                'icono'      => $m['icono'],
                'orden'      => $m['orden'],
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => $now,
            ];
        }

        $this->table('cfg_modulos')->insert($insert)->saveData();
    }
}
