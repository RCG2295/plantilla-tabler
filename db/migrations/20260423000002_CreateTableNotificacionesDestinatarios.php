<?php

use Phinx\Migration\AbstractMigration;

class CreateTableNotificacionesDestinatarios extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('admin_notificaciones_destinatarios');
        $table
            ->addColumn('id_notificacion', 'integer', [
                'null' => false,
            ])
            ->addColumn('id_usuario', 'integer', [
                'null'    => true,
                'comment' => 'NULL = broadcast to all users',
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
            ->addIndex('id_notificacion')
            ->create();
    }
}
