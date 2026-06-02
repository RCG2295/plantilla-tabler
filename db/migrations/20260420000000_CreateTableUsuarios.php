<?php

use Phinx\Migration\AbstractMigration;

class CreateTableUsuarios extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('admin_usuarios');

        $table
            ->addColumn('nombre', 'string', [
                'limit' => 100,
                'null'  => false,
            ])
            ->addColumn('apellidos', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('email', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null'  => false,
            ])
            ->addColumn('nivel', 'string', [
                'limit'   => 50,
                'default' => 'user',
                'null'    => false,
            ])
            ->addColumn('telefono', 'string', [
                'limit' => 20,
                'null'  => true,
            ])
            ->addColumn('imagen', 'string', [
                'limit' => 255,
                'null'  => true,
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
            ->addIndex('email', ['unique' => true])
            ->create();
    }
}
