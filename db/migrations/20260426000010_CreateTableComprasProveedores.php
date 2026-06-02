<?php

use Phinx\Migration\AbstractMigration;

class CreateTableComprasProveedores extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_proveedores');

        $table
            ->addColumn('nombre', 'string', [
                'limit' => 200,
                'null'  => false,
            ])
            ->addColumn('razon_social', 'string', [
                'limit' => 250,
                'null'  => true,
            ])
            ->addColumn('rfc', 'string', [
                'limit' => 20,
                'null'  => true,
            ])
            ->addColumn('telefono', 'string', [
                'limit' => 25,
                'null'  => true,
            ])
            ->addColumn('email', 'string', [
                'limit' => 150,
                'null'  => true,
            ])
            ->addColumn('direccion', 'text', [
                'null' => true,
            ])
            ->addColumn('id_categoria', 'integer', [
                'null' => true,
            ])
            ->addColumn('notas', 'text', [
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
            ->addIndex(['id_categoria'])
            ->create();
    }
}
