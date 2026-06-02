<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasTurnosCaja extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_turnos_caja')
            ->addColumn('id_usuario',      'integer',   ['null' => false])
            ->addColumn('id_sucursal',     'integer',   ['null' => true])
            ->addColumn('fondo_pesos',     'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('fondo_dolares',   'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('estado',          'integer',   ['limit' => 1, 'default' => 0, 'null' => false, 'comment' => '0=abierto,1=cerrado,2=eliminado'])
            ->addColumn('fecha_cierre',    'timestamp', ['null' => true, 'default' => null])
            ->addColumn('id_alta',         'integer',   ['null' => true])
            ->addColumn('fecha_alta',      'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_usuario'])
            ->addIndex(['id_sucursal'])
            ->create();
    }
}
