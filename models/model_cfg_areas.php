<?php

class CfgAreasModel {

    public function getAll() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, icono, orden, estado
            FROM cfg_areas
            WHERE estado != 2
            ORDER BY orden, id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, icono, orden, estado
            FROM cfg_areas
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $exclude_id = null) {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM cfg_areas WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM cfg_areas WHERE nombre = ?");
            $stmt->execute([$nombre]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO cfg_areas (nombre, icono, orden, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['icono'],
            $data['orden'],
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE cfg_areas SET nombre = ?, icono = ?, orden = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['icono'],
            $data['orden'],
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE cfg_areas SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }

}
