<?php

use Phinx\Migration\AbstractMigration;

class AddIdSucursalToCompras extends AbstractMigration
{
    public function change(): void
    {
        $this->table('compras')
            ->addColumn('id_sucursal', 'integer', ['null' => true, 'default' => null, 'after' => 'id'])
            ->addIndex(['id_sucursal'])
            ->save();
    }
}
