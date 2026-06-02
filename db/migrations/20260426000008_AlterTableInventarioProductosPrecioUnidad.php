<?php

use Phinx\Migration\AbstractMigration;

class AlterTableInventarioProductosPrecioUnidad extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_productos');

        $table
            ->addColumn('precio_venta_unidad', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'null'      => true,
                'default'   => null,
                'after'     => 'precio_venta',
            ])
            ->update();
    }
}
