<?php

use Phinx\Migration\AbstractMigration;

class CreateTableCfgAreas extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cfg_areas');

        $table
            ->addColumn('nombre', 'string', [
                'limit' => 100,
                'null'  => false,
            ])
            ->addColumn('icono', 'string', [
                'limit'   => 100,
                'null'    => false,
                'comment' => 'Full CSS class, e.g. ti ti-home',
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
            ->create();
    }
}
