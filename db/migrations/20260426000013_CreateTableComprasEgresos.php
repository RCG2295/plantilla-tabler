<?php

use Phinx\Migration\AbstractMigration;

class CreateTableComprasEgresos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_egresos');

        $table
            ->addColumn('id_compra', 'integer', [
                'null' => false,
            ])
            ->addColumn('concepto', 'string', [
                'limit' => 250,
                'null'  => false,
            ])
            ->addColumn('monto', 'decimal', [
                'precision' => 12,
                'scale'     => 2,
                'null'      => false,
            ])
            ->addColumn('estado', 'integer', [
                'limit'   => 1,
                'default' => 0,
                'null'    => false,
                'comment' => '0=active, 1=cancelled, 2=deleted',
            ])
            ->addColumn('id_alta', 'integer', [
                'null' => true,
            ])
            ->addColumn('fecha_alta', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['id_compra'])
            ->create();
    }
}
