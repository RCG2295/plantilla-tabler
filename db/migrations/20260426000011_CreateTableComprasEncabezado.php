<?php

use Phinx\Migration\AbstractMigration;

class CreateTableComprasEncabezado extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_encabezado');

        $table
            ->addColumn('folio', 'string', [
                'limit' => 30,
                'null'  => false,
            ])
            ->addColumn('fecha_compra', 'date', [
                'null' => false,
            ])
            ->addColumn('id_proveedor', 'integer', [
                'null' => true,
            ])
            ->addColumn('subtotal', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
                'comment'   => 'Sum of line subtotals before IVA',
            ])
            ->addColumn('iva_total', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
                'comment'   => 'Sum of IVA amounts per line',
            ])
            ->addColumn('total', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'default'   => '0.00',
                'null'      => false,
                'comment'   => 'subtotal + iva_total',
            ])
            ->addColumn('notas', 'text', [
                'null' => true,
            ])
            ->addColumn('estado', 'integer', [
                'limit'   => 1,
                'default' => 0,
                'null'    => false,
                'comment' => '0=activa, 1=cancelada, 2=eliminada',
            ])
            ->addColumn('id_alta', 'integer', [
                'null' => true,
            ])
            ->addColumn('fecha_alta', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['folio'], ['unique' => true])
            ->addIndex(['id_proveedor'])
            ->addIndex(['fecha_compra'])
            ->create();
    }
}
