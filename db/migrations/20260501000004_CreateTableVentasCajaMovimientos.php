<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasCajaMovimientos extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_caja_movimientos')
            ->addColumn('id_turno',     'integer',   ['null' => false])
            ->addColumn('tipo',         'string',    ['limit' => 10, 'null' => false, 'comment' => 'retiro,ingreso'])
            ->addColumn('moneda',       'string',    ['limit' => 10, 'null' => false, 'comment' => 'pesos,dolares'])
            ->addColumn('monto',        'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('descripcion',  'string',    ['limit' => 255, 'null' => true])
            ->addColumn('estado',       'integer',   ['limit' => 1, 'default' => 0, 'null' => false])
            ->addColumn('id_alta',      'integer',   ['null' => true])
            ->addColumn('fecha_alta',   'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_turno'])
            ->create();
    }
}
