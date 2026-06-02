<?php

class VentasHistorialVentasModel
{
    public function getByRango(string $desde, string $hasta, int $estado = 0): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT v.id, v.folio, v.total, v.estado, v.fecha_alta,
                   v.id_turno AS turno_id,
                   u.nombre AS cajero_nombre, u.apellidos AS cajero_apellidos,
                   s.nombre AS sucursal_nombre,
                   COALESCE(SUM(CASE WHEN fp.forma_pago = 'efectivo_pesos'   THEN fp.monto ELSE 0 END), 0) AS efectivo_pesos,
                   COALESCE(SUM(CASE WHEN fp.forma_pago = 'efectivo_dolares' THEN fp.monto ELSE 0 END), 0) AS efectivo_dolares,
                   COALESCE(SUM(CASE WHEN fp.forma_pago = 'tarjeta'          THEN fp.monto ELSE 0 END), 0) AS tarjeta,
                   COALESCE(SUM(CASE WHEN fp.forma_pago = 'transferencia'    THEN fp.monto ELSE 0 END), 0) AS transferencia
            FROM ventas v
            LEFT JOIN admin_usuarios u   ON u.id  = v.id_usuario
            LEFT JOIN admin_sucursales s ON s.id  = v.id_sucursal
            LEFT JOIN ventas_formas_pago fp ON fp.id_venta = v.id AND fp.estado != 2
            WHERE v.estado = ?
              AND DATE(v.fecha_alta) BETWEEN ? AND ?
              AND (v.id_sucursal = ? OR ? IS NULL)
            GROUP BY v.id
            ORDER BY v.fecha_alta DESC
        ");
        $stmt->execute([$estado, $desde, $hasta, $id_sucursal, $id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
