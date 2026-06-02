<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddVentasHistorialVentasModule extends AbstractMigration
{
    public function up(): void
    {
        // Insert module under Ventas area (id = 9)
        $this->execute("
            INSERT INTO cfg_modulos (id_area, nombre, clave, icono, orden, estado, id_alta, fecha_alta)
            VALUES (9, 'Historial de ventas', 'ventas/historial_ventas', 'ti ti-report', 6, 0, 1, NOW())
        ");

        // Grant full permissions to all roles
        $this->execute("
            INSERT INTO cfg_roles_permisos (id_rol, id_modulo, ver, crear, editar, eliminar, estado, id_alta, fecha_alta)
            SELECT r.id, m.id, 1, 1, 1, 1, 0, 1, NOW()
            FROM cfg_roles r
            CROSS JOIN cfg_modulos m
            WHERE m.clave = 'ventas/historial_ventas'
            ON DUPLICATE KEY UPDATE ver = 1, crear = 1, editar = 1, eliminar = 1
        ");
    }

    public function down(): void
    {
        $this->execute("
            DELETE rp FROM cfg_roles_permisos rp
            INNER JOIN cfg_modulos m ON m.id = rp.id_modulo
            WHERE m.clave = 'ventas/historial_ventas'
        ");
        $this->execute("DELETE FROM cfg_modulos WHERE clave = 'ventas/historial_ventas'");
    }
}
