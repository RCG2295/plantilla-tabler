<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasFormasPago extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_formas_pago')
            ->addColumn('id_venta',    'integer',   ['null' => false])
            ->addColumn('forma_pago',  'string',    ['limit' => 20, 'null' => false, 'comment' => 'efectivo_pesos,efectivo_dolares,tarjeta,transferencia'])
            ->addColumn('monto',       'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('monto_pesos', 'decimal',   ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => 'Amount converted to MXN'])
            ->addColumn('referencia',  'string',    ['limit' => 100, 'null' => true])
            ->addColumn('estado',      'integer',   ['limit' => 1, 'default' => 0, 'null' => false])
            ->addColumn('id_alta',     'integer',   ['null' => true])
            ->addColumn('fecha_alta',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_venta'])
            ->create();
    }
}
