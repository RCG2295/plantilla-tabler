<?php

use Phinx\Seed\AbstractSeed;

// Run after CfgRolesSeed and CfgModulosSeed
// Roles: 1=Administrador, 2=Supervisor, 3=Usuario
// Modules: 1=dashboard, 2=admin/usuarios, 3=reportes/notificaciones, 4=cfg/areas, 5=cfg/modulos, 6=cfg/roles
class CfgRolesPermisosSeed extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $rows = [];

        // Administrador (es_superadmin=1) — se setea todo a 1 por consistencia
        foreach (range(1, 6) as $mod_id) {
            $rows[] = [
                'id_rol'    => 1,
                'id_modulo' => $mod_id,
                'ver'       => 1,
                'crear'     => 1,
                'editar'    => 1,
                'eliminar'  => 1,
                'estado'    => 0,
                'id_alta'   => null,
                'fecha_alta'=> $now,
            ];
        }

        // Supervisor — puede ver todo, gestionar reportes, no toca configuración
        $supervisor_permisos = [
            1 => ['ver' => 1, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // dashboard
            2 => ['ver' => 1, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // admin/usuarios
            3 => ['ver' => 1, 'crear' => 1, 'editar' => 1, 'eliminar' => 0], // reportes/notificaciones
            4 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // cfg/areas
            5 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // cfg/modulos
            6 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // cfg/roles
        ];
        foreach ($supervisor_permisos as $mod_id => $p) {
            $rows[] = array_merge(['id_rol' => 2, 'id_modulo' => $mod_id, 'estado' => 0, 'id_alta' => null, 'fecha_alta' => $now], $p);
        }

        // Usuario — solo dashboard
        $usuario_permisos = [
            1 => ['ver' => 1, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // dashboard
            2 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // admin/usuarios
            3 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // reportes/notificaciones
            4 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // cfg/areas
            5 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // cfg/modulos
            6 => ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0], // cfg/roles
        ];
        foreach ($usuario_permisos as $mod_id => $p) {
            $rows[] = array_merge(['id_rol' => 3, 'id_modulo' => $mod_id, 'estado' => 0, 'id_alta' => null, 'fecha_alta' => $now], $p);
        }

        $this->table('cfg_roles_permisos')->insert($rows)->saveData();
    }
}
