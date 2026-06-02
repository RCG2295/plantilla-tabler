<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioUnidadesMedida extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_unidades_medida');

        $table
            ->addColumn('nombre', 'string', [
                'limit' => 100,
                'null'  => false,
            ])
            ->addColumn('abreviatura', 'string', [
                'limit' => 20,
                'null'  => false,
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
