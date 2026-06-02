<?php

use Phinx\Migration\AbstractMigration;

class AddIdSucursalToUsuarios extends AbstractMigration
{
    public function change(): void
    {
        $this->table('admin_usuarios')
            ->addColumn('id_sucursal', 'integer', ['null' => true, 'default' => null, 'after' => 'id_rol'])
            ->addIndex(['id_sucursal'])
            ->save();
    }
}
