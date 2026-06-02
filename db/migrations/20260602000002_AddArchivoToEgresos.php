<?php

use Phinx\Migration\AbstractMigration;

class AddArchivoToEgresos extends AbstractMigration
{
    public function change(): void
    {
        $this->table('egresos')
            ->addColumn('archivo', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'after' => 'notas'])
            ->save();
    }
}
