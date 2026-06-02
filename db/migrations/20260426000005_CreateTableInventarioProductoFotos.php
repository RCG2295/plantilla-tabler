<?php

use Phinx\Migration\AbstractMigration;

class CreateTableInventarioProductoFotos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inventario_producto_fotos');

        $table
            ->addColumn('id_producto', 'integer', [
                'null' => false,
            ])
            ->addColumn('nombre_archivo', 'string', [
                'limit' => 255,
                'null'  => false,
            ])
            ->addColumn('es_principal', 'boolean', [
                'default' => false,
                'null'    => false,
            ])
            ->addColumn('orden', 'integer', [
                'default' => 0,
                'null'    => false,
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
            ->addIndex(['id_producto'])
            ->create();
    }
}
