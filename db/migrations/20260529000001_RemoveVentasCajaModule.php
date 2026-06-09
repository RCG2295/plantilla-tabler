<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveVentasCajaModule extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            DELETE rp FROM cfg_roles_permisos rp
            INNER JOIN cfg_modulos m ON m.id = rp.id_modulo
            WHERE m.clave = 'ventas/caja'
        ");

        $this->execute("DELETE FROM cfg_modulos WHERE clave = 'ventas/caja'");
    }

    public function down(): void
    {
        $this->execute("
            INSERT IGNORE INTO cfg_modulos (id_area, nombre, clave, icono, orden, estado, id_alta, fecha_alta)
            SELECT a.id, 'Control de caja', 'ventas/caja', 'ti ti-building-bank', 2, 0, NULL, NOW()
            FROM cfg_areas a WHERE a.nombre = 'Ventas' LIMIT 1
        ");
    }
}
