<?php

use Phinx\Migration\AbstractMigration;

class AlterTableInventarioProductosFragments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_productos');

        $table
            ->addColumn('se_fracciona', 'boolean', [
                'default' => false,
                'null'    => false,
                'after'   => 'id_unidad_medida',
            ])
            ->addColumn('cantidad_presentacion', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '1.00',
                'null'      => false,
                'after'     => 'se_fracciona',
            ])
            ->update();
    }
}
