<?php

class InventarioMovimientosModel
{
    public function getAll($filtros = [])
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $where       = ['m.estado != 2'];
        $params      = [];

        if ($id_sucursal) {
            $where[]  = '(m.id_sucursal = ? OR m.id_sucursal IS NULL)';
            $params[] = $id_sucursal;
        }

        if (!empty($filtros['id_producto'])) {
            $where[]  = 'm.id_producto = ?';
            $params[] = (int) $filtros['id_producto'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]  = 'm.tipo = ?';
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $where[]  = 'DATE(m.fecha_alta) >= ?';
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[]  = 'DATE(m.fecha_alta) <= ?';
            $params[] = $filtros['fecha_hasta'];
        }

        $sql  = "
            SELECT m.id, m.tipo, m.cantidad, m.stock_anterior, m.stock_nuevo,
                   m.notas, m.fecha_alta,
                   p.id AS id_producto, p.codigo, p.nombre AS producto,
                   mo.nombre AS motivo,
                   u.nombre AS usuario
            FROM inventario_movimientos m
            JOIN inventario_productos p ON p.id = m.id_producto
            LEFT JOIN inventario_motivos_movimiento mo ON mo.id = m.id_motivo
            LEFT JOIN admin_usuarios u ON u.id = m.id_alta
            WHERE " . implode(' AND ', $where) . "
            ORDER BY m.fecha_alta DESC
            LIMIT 500
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductosActivos()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, codigo, nombre
            FROM inventario_productos
            WHERE estado != 2
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
