<?php

use Phinx\Seed\AbstractSeed;

class VentasSeed extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Insert Ventas area
        $this->table('cfg_areas')->insert([
            [
                'nombre'     => 'Ventas',
                'icono'      => 'ti ti-shopping-cart',
                'orden'      => 5,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => $now,
            ],
        ])->saveData();

        // Retrieve the area ID
        $rows = $this->fetchAll("SELECT id FROM cfg_areas WHERE nombre = 'Ventas' ORDER BY id DESC LIMIT 1");
        if (empty($rows)) return;
        $id_area = (int) $rows[0]['id'];

        // Insert modules
        $modulos = [
            ['clave' => 'ventas/tipo_cambio', 'nombre' => 'Tipo de Cambio',   'orden' => 1],
            ['clave' => 'ventas/caja',        'nombre' => 'Control de Caja',  'orden' => 2],
            ['clave' => 'ventas/mi_caja',     'nombre' => 'Mi Caja',          'orden' => 3],
            ['clave' => 'ventas/pos',         'nombre' => 'Punto de Venta',   'orden' => 4],
        ];

        foreach ($modulos as $m) {
            $this->table('cfg_modulos')->insert([
                [
                    'id_area'    => $id_area,
                    'clave'      => $m['clave'],
                    'nombre'     => $m['nombre'],
                    'icono'      => null,
                    'orden'      => $m['orden'],
                    'estado'     => 0,
                    'id_alta'    => null,
                    'fecha_alta' => $now,
                ],
            ])->saveData();
        }

        // Assign all permissions to all roles for these modules
        $roles = $this->fetchAll("SELECT id FROM cfg_roles WHERE estado = 0");
        $mods  = $this->fetchAll(
            "SELECT id FROM cfg_modulos WHERE clave IN ('ventas/tipo_cambio','ventas/caja','ventas/mi_caja','ventas/pos') AND estado = 0"
        );

        foreach ($roles as $rol) {
            foreach ($mods as $mod) {
                $exists = $this->fetchAll(
                    "SELECT id FROM cfg_roles_permisos WHERE id_rol = {$rol['id']} AND id_modulo = {$mod['id']} LIMIT 1"
                );
                if ($exists) continue;
                $this->table('cfg_roles_permisos')->insert([
                    [
                        'id_rol'     => $rol['id'],
                        'id_modulo'  => $mod['id'],
                        'ver'        => 1,
                        'crear'      => 1,
                        'editar'     => 1,
                        'eliminar'   => 1,
                        'estado'     => 0,
                        'id_alta'    => null,
                        'fecha_alta' => $now,
                    ],
                ])->saveData();
            }
        }
    }
}
