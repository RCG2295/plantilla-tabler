<?php

use Phinx\Migration\AbstractMigration;

class CreateTableCfgRolesPermisos extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cfg_roles_permisos');

        $table
            ->addColumn('id_rol', 'integer', [
                'null' => false,
            ])
            ->addColumn('id_modulo', 'integer', [
                'null' => false,
            ])
            ->addColumn('ver', 'boolean', [
                'default' => false,
                'null'    => false,
            ])
            ->addColumn('crear', 'boolean', [
                'default' => false,
                'null'    => false,
            ])
            ->addColumn('editar', 'boolean', [
                'default' => false,
                'null'    => false,
            ])
            ->addColumn('eliminar', 'boolean', [
                'default' => false,
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
            ->addIndex(['id_rol', 'id_modulo'], ['unique' => true])
            ->create();
    }
}
