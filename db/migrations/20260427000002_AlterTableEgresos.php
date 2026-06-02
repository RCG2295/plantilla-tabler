<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterTableEgresos extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE egresos
                ADD COLUMN id_categoria    INT UNSIGNED NULL DEFAULT NULL AFTER id_compra,
                ADD COLUMN id_subcategoria INT UNSIGNED NULL DEFAULT NULL AFTER id_categoria,
                ADD COLUMN fecha_egreso    DATE         NULL DEFAULT NULL AFTER id_subcategoria,
                ADD COLUMN metodo_pago     VARCHAR(20)  NOT NULL DEFAULT 'efectivo' AFTER fecha_egreso,
                ADD COLUMN referencia      VARCHAR(150) NULL DEFAULT NULL AFTER metodo_pago,
                ADD COLUMN notas           TEXT         NULL DEFAULT NULL AFTER referencia,
                ADD CONSTRAINT fk_egreso_categoria    FOREIGN KEY (id_categoria)    REFERENCES egresos_categorias(id) ON DELETE SET NULL,
                ADD CONSTRAINT fk_egreso_subcategoria FOREIGN KEY (id_subcategoria) REFERENCES egresos_categorias(id) ON DELETE SET NULL;
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE egresos
                DROP FOREIGN KEY fk_egreso_categoria,
                DROP FOREIGN KEY fk_egreso_subcategoria,
                DROP COLUMN id_categoria,
                DROP COLUMN id_subcategoria,
                DROP COLUMN fecha_egreso,
                DROP COLUMN metodo_pago,
                DROP COLUMN referencia,
                DROP COLUMN notas;
        ");
    }
}
