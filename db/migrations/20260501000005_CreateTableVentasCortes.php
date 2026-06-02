<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentasCortes extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas_cortes')
            ->addColumn('id_turno',                  'integer',   ['null' => false])
            ->addColumn('total_ventas',               'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('total_efectivo_pesos',       'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('total_efectivo_dolares',     'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('total_tarjeta',              'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('total_transferencia',        'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('efectivo_esperado_pesos',    'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('efectivo_declarado_pesos',   'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('diferencia_pesos',           'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('efectivo_esperado_dolares',  'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('efectivo_declarado_dolares', 'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('diferencia_dolares',         'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('tipo_cambio_usado',          'decimal',   ['precision' => 10, 'scale' => 4, 'default' => '0.0000'])
            ->addColumn('estado',                     'integer',   ['limit' => 1, 'default' => 0, 'null' => false])
            ->addColumn('id_alta',                    'integer',   ['null' => true])
            ->addColumn('fecha_alta',                 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_turno'])
            ->create();
    }
}
