<?php

use Phinx\Migration\AbstractMigration;

class AddArchivoToCompras extends AbstractMigration
{
    public function change(): void
    {
        $this->table('compras')
            ->addColumn('archivo', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'after' => 'notas'])
            ->save();
    }
}
