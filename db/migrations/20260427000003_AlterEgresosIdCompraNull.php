<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterEgresosIdCompraNull extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE egresos MODIFY COLUMN id_compra INT(11) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        $this->execute("UPDATE egresos SET id_compra = 0 WHERE id_compra IS NULL");
        $this->execute("ALTER TABLE egresos MODIFY COLUMN id_compra INT(11) NOT NULL");
    }
}
