<?php

use Phinx\Migration\AbstractMigration;

class AddIdSucursalToEgresos extends AbstractMigration
{
    public function change(): void
    {
        $this->table('egresos')
            ->addColumn('id_sucursal', 'integer', ['null' => true, 'default' => null, 'after' => 'id'])
            ->addIndex(['id_sucursal'])
            ->save();
    }
}
