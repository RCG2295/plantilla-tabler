<?php

use Phinx\Seed\AbstractSeed;

class UsuarioAdminSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['CfgRolesSeed'];
    }

    public function run(): void
    {
        $this->table('admin_usuarios')->insert([
            'nombre'     => 'Administrador',
            'apellidos'  => 'Sistema',
            'email'      => 'admin@sistema.com',
            'password'   => password_hash('admin123', PASSWORD_BCRYPT),
            'id_rol'     => 1,
            'telefono'   => null,
            'imagen'     => null,
            'estado'     => 0,
            'id_alta'    => null,
            'fecha_alta' => date('Y-m-d H:i:s'),
        ])->saveData();
    }
}
