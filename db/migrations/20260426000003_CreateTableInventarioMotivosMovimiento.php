<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioMotivosMovimiento extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_motivos_movimiento');

        $table
            ->addColumn('nombre', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('tipo', 'string', [
                'limit'   => 10,
                'null'    => false,
                'comment' => 'entrada, salida, ambos',
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
            ->create();
    }
}
