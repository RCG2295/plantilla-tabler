<?php

class CfgRolesModel {

    public function getAll() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, descripcion, es_superadmin, estado
            FROM cfg_roles
            WHERE estado != 2
            ORDER BY id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, descripcion, es_superadmin, estado
            FROM cfg_roles
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // For user form selects
    public function getSelect() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre FROM cfg_roles WHERE estado = 0 ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $exclude_id = null) {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM cfg_roles WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM cfg_roles WHERE nombre = ?");
            $stmt->execute([$nombre]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO cfg_roles (nombre, descripcion, es_superadmin, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['es_superadmin'] ? 1 : 0,
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE cfg_roles SET nombre = ?, descripcion = ?, es_superadmin = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['es_superadmin'] ? 1 : 0,
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE cfg_roles SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Returns all modules with the role's current permissions (LEFT JOIN so all modules appear)
    public function getPermisos($id_rol) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT a.id AS area_id, a.nombre AS area_nombre, a.orden AS area_orden,
                   m.id AS modulo_id, m.clave, m.nombre AS modulo_nombre, m.orden AS modulo_orden,
                   COALESCE(rp.ver,      0) AS ver,
                   COALESCE(rp.crear,    0) AS crear,
                   COALESCE(rp.editar,   0) AS editar,
                   COALESCE(rp.eliminar, 0) AS eliminar
            FROM cfg_modulos m
            JOIN cfg_areas a ON a.id = m.id_area
            LEFT JOIN cfg_roles_permisos rp ON rp.id_modulo = m.id AND rp.id_rol = ?
            WHERE m.estado = 0 AND a.estado = 0
            ORDER BY a.orden, m.orden
        ");
        $stmt->execute([$id_rol]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by area
        $areas = [];
        foreach ($rows as $row) {
            $aid = $row['area_id'];
            if (!isset($areas[$aid])) {
                $areas[$aid] = [
                    'id'      => $aid,
                    'nombre'  => $row['area_nombre'],
                    'orden'   => $row['area_orden'],
                    'modulos' => [],
                ];
            }
            $areas[$aid]['modulos'][] = [
                'id'       => $row['modulo_id'],
                'clave'    => $row['clave'],
                'nombre'   => $row['modulo_nombre'],
                'ver'      => (int) $row['ver'],
                'crear'    => (int) $row['crear'],
                'editar'   => (int) $row['editar'],
                'eliminar' => (int) $row['eliminar'],
            ];
        }

        return array_values($areas);
    }

    // Replaces all permissions for a role in a single transaction
    public function savePermisos($id_rol, array $permisos, $id_alta) {
        $db = Connection::connect();
        $db->beginTransaction();
        try {
            $del = $db->prepare("DELETE FROM cfg_roles_permisos WHERE id_rol = ?");
            $del->execute([$id_rol]);

            $ins = $db->prepare("
                INSERT INTO cfg_roles_permisos (id_rol, id_modulo, ver, crear, editar, eliminar, estado, id_alta, fecha_alta)
                VALUES (?, ?, ?, ?, ?, ?, 0, ?, NOW())
            ");

            foreach ($permisos as $p) {
                $ver      = (int) ($p['ver']      ?? 0);
                $crear    = (int) ($p['crear']    ?? 0);
                $editar   = (int) ($p['editar']   ?? 0);
                $eliminar = (int) ($p['eliminar'] ?? 0);

                // Enforce: any action implies ver
                if ($crear || $editar || $eliminar) $ver = 1;

                $ins->execute([
                    $id_rol,
                    (int) $p['id_modulo'],
                    $ver,
                    $crear,
                    $editar,
                    $eliminar,
                    $id_alta,
                ]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

}
