<?php

use Phinx\Seed\AbstractSeed;

// Run after CfgRolesSeed, CfgAreasSeed and CfgModulosSeed.
// Creates the admin/sucursales module and grants full permissions to superadmin roles only.
class AdminSucursalesSeed extends AbstractSeed
{
    public function run(): void
    {
        $db = $this->getAdapter()->getConnection();

        // Find or create the Administracion area
        $stmt = $db->query("SELECT id FROM cfg_areas WHERE nombre LIKE '%dmin%' LIMIT 1");
        $area = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$area) {
            $db->exec("
                INSERT INTO cfg_areas (nombre, icono, orden, estado, id_alta, fecha_alta)
                VALUES ('Administración', 'ti ti-settings', 1, 0, NULL, NOW())
            ");
            $id_area = (int) $db->lastInsertId();
        } else {
            $id_area = (int) $area['id'];
        }

        // Avoid duplicate
        $check = $db->prepare("SELECT id FROM cfg_modulos WHERE clave = 'admin/sucursales'");
        $check->execute();
        if ($check->fetch()) {
            echo "El modulo admin/sucursales ya existe. Seed omitido.\n";
            return;
        }

        $ordenStmt = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS next FROM cfg_modulos WHERE id_area = ?");
        $ordenStmt->execute([$id_area]);
        $nextOrden = (int) $ordenStmt->fetch(PDO::FETCH_ASSOC)['next'];

        $db->prepare("
            INSERT INTO cfg_modulos (id_area, clave, nombre, icono, orden, estado, id_alta, fecha_alta)
            VALUES (?, 'admin/sucursales', 'Sucursales', 'ti ti-building', ?, 0, NULL, NOW())
        ")->execute([$id_area, $nextOrden]);

        $id_modulo = (int) $db->lastInsertId();

        // Grant full permissions only to superadmin roles
        $roles = $db->query("SELECT id, es_superadmin FROM cfg_roles")->fetchAll(PDO::FETCH_ASSOC);
        $stmt  = $db->prepare("
            INSERT INTO cfg_roles_permisos (id_rol, id_modulo, ver, crear, editar, eliminar, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, NULL, NOW())
        ");
        foreach ($roles as $rol) {
            $es_super = (int) $rol['es_superadmin'];
            $stmt->execute([$rol['id'], $id_modulo, $es_super, $es_super, $es_super, $es_super]);
        }

        echo "Modulo admin/sucursales creado. Permisos completos solo para superadmin.\n";
    }
}
