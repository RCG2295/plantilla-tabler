<?php

use Phinx\Seed\AbstractSeed;

class CfgRolesSeed extends AbstractSeed
{
    public function run(): void
    {
        $this->table('cfg_roles')->insert([
            [
                'nombre'       => 'Administrador',
                'descripcion'  => 'Acceso total al sistema',
                'es_superadmin'=> 1,
                'estado'       => 0,
                'id_alta'      => null,
                'fecha_alta'   => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'       => 'Supervisor',
                'descripcion'  => 'Puede ver y gestionar la mayoría de módulos',
                'es_superadmin'=> 0,
                'estado'       => 0,
                'id_alta'      => null,
                'fecha_alta'   => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'       => 'Usuario',
                'descripcion'  => 'Acceso básico al sistema',
                'es_superadmin'=> 0,
                'estado'       => 0,
                'id_alta'      => null,
                'fecha_alta'   => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }
}
