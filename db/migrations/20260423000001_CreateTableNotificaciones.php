<?php

use Phinx\Migration\AbstractMigration;

class CreateTableNotificaciones extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('admin_notificaciones');
        $table
            ->addColumn('titulo', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('mensaje', 'text', [
                'null' => false,
            ])
            ->addColumn('id_alta', 'integer', [
                'null' => false,
            ])
            ->addColumn('estado', 'integer', [
                'limit'   => 1,
                'default' => 0,
                'null'    => false,
                'comment' => '0=active, 1=inactive, 2=deleted',
            ])
            ->addColumn('fecha_alta', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->create();
    }
}
