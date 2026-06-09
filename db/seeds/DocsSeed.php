<?php

use Phinx\Seed\AbstractSeed;

class DocsSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['CfgAreasSeed', 'CfgRolesSeed'];
    }

    public function run(): void
    {
        $db = $this->getAdapter()->getConnection();

        // Find or create "Ayuda" area
        $stmt = $db->prepare("SELECT id FROM cfg_areas WHERE nombre = 'Ayuda' LIMIT 1");
        $stmt->execute();
        $area = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$area) {
            $db->prepare("
                INSERT INTO cfg_areas (nombre, icono, orden, estado, id_alta, fecha_alta)
                VALUES ('Ayuda', 'ti ti-help', 99, 0, NULL, NOW())
            ")->execute();
            $area_id = (int) $db->lastInsertId();
        } else {
            $area_id = (int) $area['id'];
        }

        // Insert module if not exists
        $stmt = $db->prepare("SELECT id FROM cfg_modulos WHERE clave = 'docs' LIMIT 1");
        $stmt->execute();
        $modulo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$modulo) {
            $db->prepare("
                INSERT INTO cfg_modulos (id_area, nombre, clave, icono, orden, estado, id_alta, fecha_alta)
                VALUES (?, 'Manual del Sistema', 'docs', 'ti ti-book', 1, 0, NULL, NOW())
            ")->execute([$area_id]);
            $modulo_id = (int) $db->lastInsertId();
        } else {
            $modulo_id = (int) $modulo['id'];
        }

        // Grant ver=1 to all roles (read-only module)
        $stmt = $db->query("SELECT id FROM cfg_roles WHERE estado != 2");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($roles as $rol) {
            $db->prepare("
                INSERT INTO cfg_roles_permisos (id_rol, id_modulo, ver, crear, editar, eliminar)
                VALUES (?, ?, 1, 0, 0, 0)
                ON DUPLICATE KEY UPDATE ver = 1, crear = 0, editar = 0, eliminar = 0
            ")->execute([$rol['id'], $modulo_id]);
        }

        echo "DocsSeed: módulo 'docs' registrado con área 'Ayuda' y permisos de lectura para todos los roles.\n";
    }
}
