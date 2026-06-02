<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioStock extends AbstractMigration
{
    public function change(): void
    {
        $this->table('inventario_stock')
            ->addColumn('id_producto',  'integer', [])
            ->addColumn('id_sucursal',  'integer', [])
            ->addColumn('stock_actual', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('estado',       'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0])
            ->addColumn('id_alta',      'integer', ['null' => true, 'default' => null])
            ->addColumn('fecha_alta',   'datetime')
            ->addIndex(['id_producto', 'id_sucursal'], ['unique' => true, 'name' => 'uk_stock_producto_sucursal'])
            ->addIndex(['id_sucursal'])
            ->create();
    }
}
