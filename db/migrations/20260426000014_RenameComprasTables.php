<?php

use Phinx\Migration\AbstractMigration;

class RenameComprasTables extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('RENAME TABLE compras_encabezado TO compras');
        $this->execute('RENAME TABLE compras_detalle    TO compras_items');
        $this->execute('RENAME TABLE compras_egresos    TO egresos');
    }

    public function down(): void
    {
        $this->execute('RENAME TABLE compras       TO compras_encabezado');
        $this->execute('RENAME TABLE compras_items TO compras_detalle');
        $this->execute('RENAME TABLE egresos       TO compras_egresos');
    }
}
