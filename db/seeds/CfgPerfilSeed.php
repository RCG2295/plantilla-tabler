<?php

use Phinx\Seed\AbstractSeed;

// Run after CfgRolesSeed and CfgModulosSeed.
// Inserts the cfg/perfil module and grants view permission to all roles.
class CfgPerfilSeed extends AbstractSeed
{
    public function run(): void
    {
        $db = $this->getAdapter()->getConnection();

        // Find the Configuracion area (assumes it already exists)
        $stmt = $db->query("SELECT id FROM cfg_areas WHERE nombre LIKE '%onfig%' LIMIT 1");
        $area = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$area) {
            echo "No se encontro el area de Configuracion. Asegurate de haber corrido CfgAreasSeed primero.\n";
            return;
        }

        $id_area = $area['id'];

        // Avoid duplicate insertion
        $check = $db->prepare("SELECT id FROM cfg_modulos WHERE clave = 'cfg/perfil'");
        $check->execute();
        if ($check->fetch()) {
            echo "El modulo cfg/perfil ya existe. Seed omitido.\n";
            return;
        }

        // Get max orden within the area
        $ordenStmt = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM cfg_modulos WHERE id_area = ?");
        $ordenStmt->execute([$id_area]);
        $nextOrden = (int) $ordenStmt->fetch(PDO::FETCH_ASSOC)['next_orden'];

        $db->prepare("
            INSERT INTO cfg_modulos (id_area, clave, nombre, icono, orden, estado, id_alta, fecha_alta)
            VALUES (?, 'cfg/perfil', 'Mi perfil', 'ti ti-user-circle', ?, 0, NULL, NOW())
        ")->execute([$id_area, $nextOrden]);

        $id_modulo = $db->lastInsertId();

        // Grant ver + editar to all roles
        $roles = $db->query("SELECT id FROM cfg_roles")->fetchAll(PDO::FETCH_ASSOC);
        $stmt  = $db->prepare("
            INSERT INTO cfg_roles_permisos (id_rol, id_modulo, ver, crear, editar, eliminar, id_alta, fecha_alta)
            VALUES (?, ?, 1, 0, 1, 0, NULL, NOW())
        ");
        foreach ($roles as $rol) {
            $stmt->execute([$rol['id'], $id_modulo]);
        }

        echo "Modulo cfg/perfil creado y permisos asignados a todos los roles.\n";
    }
}
