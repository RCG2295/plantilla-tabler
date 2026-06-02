<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioCategorias extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_categorias');

        $table
            ->addColumn('nombre', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('descripcion', 'string', [
                'limit' => 255,
                'null'  => true,
            ])
            ->addColumn('id_padre', 'integer', [
                'null' => true,
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
            ->addIndex(['id_padre'])
            ->create();
    }
}
