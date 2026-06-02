<?php

class VentasTipoCambioModel
{
    public function getAll()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT tc.id, tc.valor, tc.fecha_alta,
                   u.nombre AS usuario
            FROM ventas_tipo_cambio tc
            LEFT JOIN admin_usuarios u ON u.id = tc.id_alta
            WHERE tc.estado != 2
            ORDER BY tc.fecha_alta DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVigente()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, valor, fecha_alta
            FROM ventas_tipo_cambio
            WHERE estado != 2
            ORDER BY fecha_alta DESC
            LIMIT 1
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insert($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO ventas_tipo_cambio (valor, estado, id_alta, fecha_alta)
            VALUES (?, 0, ?, NOW())
        ");
        return $stmt->execute([$data['valor'], $data['id_alta'] ?? null]);
    }
}
