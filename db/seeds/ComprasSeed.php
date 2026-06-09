<?php

use Phinx\Seed\AbstractSeed;

class ComprasSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['CfgAreasSeed', 'CfgRolesSeed'];
    }

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('cfg_areas')->insert([
            [
                'nombre'     => 'Compras',
                'icono'      => 'ti ti-shopping-bag',
                'orden'      => 7,
                'estado'     => 0,
                'id_alta'    => null,
                'fecha_alta' => $now,
            ],
        ])->saveData();

        $rows = $this->fetchAll("SELECT id FROM cfg_areas WHERE nombre = 'Compras' ORDER BY id DESC LIMIT 1");
        if (empty($rows)) return;
        $id_area = (int) $rows[0]['id'];

        $modulos = [
            ['clave' => 'compras/proveedores', 'nombre' => 'Proveedores', 'orden' => 1],
            ['clave' => 'compras/compras',     'nombre' => 'Compras',     'orden' => 2],
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

        $roles = $this->fetchAll("SELECT id FROM cfg_roles WHERE estado = 0");
        $mods  = $this->fetchAll(
            "SELECT id FROM cfg_modulos WHERE clave IN ('compras/proveedores','compras/compras') AND estado = 0"
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
