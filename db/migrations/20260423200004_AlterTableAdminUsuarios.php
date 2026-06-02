<?php

use Phinx\Migration\AbstractMigration;

class AlterTableAdminUsuarios extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('admin_usuarios');

        $table
            ->addColumn('id_rol', 'integer', [
                'null'  => true,
                'after' => 'password',
            ])
            ->save();

        $table->removeColumn('nivel')->save();
    }

    public function down(): void
    {
        $table = $this->table('admin_usuarios');

        $table->removeColumn('id_rol')->save();

        $table
            ->addColumn('nivel', 'string', [
                'limit'   => 50,
                'default' => 'user',
                'null'    => false,
            ])
            ->save();
    }
}
