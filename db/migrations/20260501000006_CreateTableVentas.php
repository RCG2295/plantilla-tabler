<?php

use Phinx\Migration\AbstractMigration;

class CreateTableVentas extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ventas')
            ->addColumn('id_turno',    'integer',   ['null' => true])
            ->addColumn('id_usuario',  'integer',   ['null' => false])
            ->addColumn('id_sucursal', 'integer',   ['null' => true])
            ->addColumn('folio',       'string',    ['limit' => 20, 'null' => true])
            ->addColumn('subtotal',    'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('total',       'decimal',   ['precision' => 10, 'scale' => 2, 'default' => '0.00'])
            ->addColumn('tipo_cambio', 'decimal',   ['precision' => 10, 'scale' => 4, 'default' => '0.0000'])
            ->addColumn('estado',      'integer',   ['limit' => 1, 'default' => 0, 'null' => false, 'comment' => '0=activa,1=cancelada,2=eliminada'])
            ->addColumn('id_alta',     'integer',   ['null' => true])
            ->addColumn('fecha_alta',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['id_turno'])
            ->addIndex(['id_usuario'])
            ->addIndex(['id_sucursal'])
            ->create();
    }
}
