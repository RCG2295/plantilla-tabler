<?php

use Phinx\Migration\AbstractMigration;

class AddIdSucursalToMovimientos extends AbstractMigration
{
    public function change(): void
    {
        $this->table('inventario_movimientos')
            ->addColumn('id_sucursal', 'integer', ['null' => true, 'default' => null, 'after' => 'id_producto'])
            ->addIndex(['id_sucursal'])
            ->save();
    }
}
