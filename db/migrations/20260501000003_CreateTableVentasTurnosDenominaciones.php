<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasTurnosDenominaciones extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_turnos_denominaciones')
            ->addColumn('id_turno',      'integer',   ['null' => false])
            ->addColumn('momento',       'string',    ['limit' => 10, 'null' => false, 'comment' => 'apertura,cierre'])
            ->addColumn('moneda',        'string',    ['limit' => 10, 'null' => false, 'comment' => 'pesos,dolares'])
            ->addColumn('tipo',          'string',    ['limit' => 10, 'null' => false, 'comment' => 'billete,moneda'])
            ->addColumn('denominacion',  'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('cantidad',      'integer',   ['default' => 0, 'null' => false])
            ->addColumn('estado',        'integer',   ['limit' => 1, 'default' => 0, 'null' => false])
            ->addColumn('id_alta',       'integer',   ['null' => true])
            ->addColumn('fecha_alta',    'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_turno'])
            ->create();
    }
}
