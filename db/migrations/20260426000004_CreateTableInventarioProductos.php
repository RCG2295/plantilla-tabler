<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioProductos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_productos');

        $table
            ->addColumn('codigo', 'string', [
                'limit' => 100,
                'null'  => false,
            ])
            ->addColumn('nombre', 'string', [
                'limit' => 200,
                'null'  => false,
            ])
            ->addColumn('descripcion', 'text', [
                'null' => true,
            ])
            ->addColumn('id_categoria', 'integer', [
                'null' => true,
            ])
            ->addColumn('id_unidad_medida', 'integer', [
                'null' => true,
            ])
            ->addColumn('stock_actual', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
            ])
            ->addColumn('stock_minimo', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
            ])
            ->addColumn('stock_maximo', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
            ])
            ->addColumn('precio_costo', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
            ])
            ->addColumn('precio_venta', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
            ])
            ->addColumn('estado', 'integer', [
                'limit'   => 1,
                'default' => 0,
                'null'    => false,
                'comment' => '0=active, 1=inactive, 2=deleted',
            ])
            ->addColumn('id_alta', 'integer', [
                'null' => true,
            ])
            ->addColumn('fecha_alta', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['codigo'], ['unique' => true])
            ->addIndex(['id_categoria'])
            ->addIndex(['id_unidad_medida'])
            ->create();
    }
}
