<?php

class CfgModulosModel {

    public function getAll() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT m.id, m.id_area, a.nombre AS area_nombre, m.clave, m.nombre,
                   m.icono, m.orden, m.estado
            FROM cfg_modulos m
            JOIN cfg_areas a ON a.id = m.id_area
            WHERE m.estado != 2
            ORDER BY a.orden, m.orden
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, id_area, clave, nombre, icono, orden, estado
            FROM cfg_modulos
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function claveExiste($clave, $exclude_id = null) {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM cfg_modulos WHERE clave = ? AND id != ?");
            $stmt->execute([$clave, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM cfg_modulos WHERE clave = ?");
            $stmt->execute([$clave]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO cfg_modulos (id_area, clave, nombre, icono, orden, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['id_area'],
            $data['clave'],
            $data['nombre'],
            $data['icono'] ?: null,
            $data['orden'],
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE cfg_modulos
            SET id_area = ?, clave = ?, nombre = ?, icono = ?, orden = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['id_area'],
            $data['clave'],
            $data['nombre'],
            $data['icono'] ?: null,
            $data['orden'],
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE cfg_modulos SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Returns areas with their visible modules, ordered — used by sidebar
    public static function getSidebarMenu() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT a.id AS area_id, a.nombre AS area_nombre, a.icono AS area_icono, a.orden AS area_orden,
                   m.id AS mod_id, m.clave, m.nombre AS mod_nombre, m.icono AS mod_icono, m.orden AS mod_orden
            FROM cfg_areas a
            JOIN cfg_modulos m ON m.id_area = a.id AND m.estado = 0
            WHERE a.estado = 0
            ORDER BY a.orden, m.orden
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $areas = [];
        foreach ($rows as $row) {
            $aid = $row['area_id'];
            if (!isset($areas[$aid])) {
                $areas[$aid] = [
                    'id'      => $aid,
                    'nombre'  => $row['area_nombre'],
                    'icono'   => $row['area_icono'],
                    'orden'   => $row['area_orden'],
                    'modulos' => [],
                ];
            }
            $areas[$aid]['modulos'][] = [
                'id'     => $row['mod_id'],
                'clave'  => $row['clave'],
                'nombre' => $row['mod_nombre'],
                'icono'  => $row['mod_icono'],
                'orden'  => $row['mod_orden'],
            ];
        }

        return array_values($areas);
    }

}
