<?php

use Phinx\Migration\AbstractMigration;

class DropStockActualFromProductos extends AbstractMigration
{
    public function up(): void
    {
        $this->table('inventario_productos')
            ->removeColumn('stock_actual')
            ->save();
    }

    public function down(): void
    {
        $this->table('inventario_productos')
            ->addColumn('stock_actual', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'after'     => 'id_unidad_medida',
            ])
            ->save();
    }
}
