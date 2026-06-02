<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioMovimientos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_movimientos');

        $table
            ->addColumn('id_producto', 'integer', [
                'null' => false,
            ])
            ->addColumn('tipo', 'string', [
                'limit'   => 10,
                'null'    => false,
                'comment' => 'entrada, salida',
            ])
            ->addColumn('cantidad', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'null'      => false,
            ])
            ->addColumn('stock_anterior', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'null'      => false,
            ])
            ->addColumn('stock_nuevo', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'null'      => false,
            ])
            ->addColumn('id_motivo', 'integer', [
                'null' => true,
            ])
            ->addColumn('notas', 'text', [
                'null' => true,
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
            ->addIndex(['id_producto'])
            ->addIndex(['id_motivo'])
            ->create();
    }
}
