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
        // Re-insertar el módulo bajo el área de Ventas (id_area = 9)
        $this->execute("
            INSERT INTO cfg_modulos (id_area, nombre, clave, icono, orden, estado, id_alta, fecha_alta)
            VALUES (9, 'Control de caja', 'ventas/caja', 'ti ti-building-bank', 2, 0, 1, NOW())
        ");
    }
}
