<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasItems extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_items')
            ->addColumn('id_venta',        'integer',   ['null' => false])
            ->addColumn('id_producto',     'integer',   ['null' => false])
            ->addColumn('tipo_precio',     'string',    ['limit' => 15, 'default' => 'presentacion', 'null' => false, 'comment' => 'presentacion,unidad'])
            ->addColumn('cantidad',        'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('precio_unitario', 'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('subtotal',        'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('estado',          'integer',   ['limit' => 1, 'default' => 0, 'null' => false])
            ->addColumn('id_alta',         'integer',   ['null' => true])
            ->addColumn('fecha_alta',      'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_venta'])
            ->addIndex(['id_producto'])
            ->create();
    }
}
