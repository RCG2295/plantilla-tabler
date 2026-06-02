<?php

use Phinx\Migration\AbstractMigration;

class CreateTableAdminSucursales extends AbstractMigration
{
    public function change(): void
    {
        $this->table('admin_sucursales')
            ->addColumn('nombre',    'string',  ['limit' => 150])
            ->addColumn('direccion', 'string',  ['limit' => 300, 'null' => true, 'default' => null])
            ->addColumn('telefono',  'string',  ['limit' => 20,  'null' => true, 'default' => null])
            ->addColumn('estado',    'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0])
            ->addColumn('id_alta',   'integer', ['null' => true, 'default' => null])
            ->addColumn('fecha_alta','datetime')
            ->create();
    }
}
