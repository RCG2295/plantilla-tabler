<?php

use Phinx\Migration\AbstractMigration;

class CreateTableCfgModulos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cfg_modulos');

        $table
            ->addColumn('id_area', 'integer', [
                'null' => false,
            ])
            ->addColumn('clave', 'string', [
                'limit'   => 100,
                'null'    => false,
                'comment' => 'Matches route, e.g. admin/usuarios',
            ])
            ->addColumn('nombre', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('icono', 'string', [
                'limit' => 100,
                'null'  => true,
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
            ->addIndex('clave', ['unique' => true])
            ->create();
    }
}
