<?php

use Phinx\Migration\AbstractMigration;

class CreateTableComprasDetalle extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_detalle');

        $table
            ->addColumn('id_compra', 'integer', [
                'null' => false,
            ])
            ->addColumn('id_producto', 'integer', [
                'null' => false,
            ])
            ->addColumn('cantidad', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'null'      => false,
                'comment'   => 'Quantity as entered (presentations if fraccionable)',
            ])
            ->addColumn('cantidad_base', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'null'      => false,
                'comment'   => 'Base units added to stock',
            ])
            ->addColumn('precio_unitario', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
            ])
            ->addColumn('iva', 'decimal', [
                'precision' => 5,
                'scale'     => 2,
                'default'   => '8.00',
                'null'      => false,
                'comment'   => 'IVA percentage (e.g. 8, 16, 0)',
            ])
            ->addColumn('subtotal', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
                'comment'   => 'cantidad * precio_unitario',
            ])
            ->addColumn('iva_monto', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
                'comment'   => 'subtotal * iva / 100',
            ])
            ->addColumn('total_linea', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
                'comment'   => 'subtotal + iva_monto',
            ])
            ->addColumn('estado', 'integer', [
                'limit'   => 1,
                'default' => 0,
                'null'    => false,
                'comment' => '0=active, 2=deleted',
            ])
            ->addColumn('id_alta', 'integer', [
                'null' => true,
            ])
            ->addColumn('fecha_alta', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['id_compra'])
            ->addIndex(['id_producto'])
            ->create();
    }
}
