<?php

use Phinx\Migration\AbstractMigration;

class DropComprasCategorias extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('ALTER TABLE compras_proveedores DROP COLUMN id_categoria');
        $this->table('compras_categorias')->drop()->save();
    }

    public function down(): void
    {
        $this->table('compras_categorias')
            ->addColumn('nombre', 'string', ['limit' => 150])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('id_padre', 'integer', ['null' => true])
            ->addColumn('estado', 'integer', ['limit' => 1, 'default' => 0])
            ->addColumn('id_alta', 'integer', ['null' => true])
            ->addColumn('fecha_alta', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $this->execute('ALTER TABLE compras_proveedores ADD COLUMN id_categoria INT NULL AFTER direccion');
    }
}
