<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableEgresosCategorias extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE egresos_categorias (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nombre      VARCHAR(150) NOT NULL,
                descripcion VARCHAR(500) NULL,
                id_padre    INT UNSIGNED NULL DEFAULT NULL,
                estado      TINYINT UNSIGNED NOT NULL DEFAULT 0,
                id_alta     INT UNSIGNED NOT NULL,
                fecha_alta  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_egcat_padre FOREIGN KEY (id_padre)
                    REFERENCES egresos_categorias(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS egresos_categorias');
    }
}
