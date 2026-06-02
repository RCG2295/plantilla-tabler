<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasTipoCambio extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_tipo_cambio')
            ->addColumn('valor',      'decimal',   ['precision' => 10, 'scale' => 4, 'null' => false])
            ->addColumn('estado',     'integer',   ['limit' => 1, 'default' => 0, 'null' => false])
            ->addColumn('id_alta',    'integer',   ['null' => true])
            ->addColumn('fecha_alta', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();
    }
}
