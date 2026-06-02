<?php

class DashboardModel
{
    private function db(): PDO { return Connection::connect(); }

    private function suc(): ?int
    {
        $id = $_SESSION['id_sucursal'] ?? null;
        return $id ? (int) $id : null;
    }

    public function semana(): array
    {
        $dow     = (int) date('N');
        $lunes   = date('Y-m-d', strtotime('-' . ($dow - 1) . ' days'));
        $domingo = date('Y-m-d', strtotime('+' . (7 - $dow)  . ' days'));
        return ['desde' => $lunes, 'hasta' => $domingo, 'hoy' => date('Y-m-d')];
    }

    public function getResumenVentas(): array
    {
        $s = $this->semana(); $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT COUNT(DISTINCT v.id)                                                                     AS num_ventas,
                   COALESCE(SUM(v.total), 0)                                                               AS total_ventas,
                   COALESCE(SUM(CASE WHEN fp.forma_pago='efectivo_pesos'   THEN fp.monto_pesos ELSE 0 END),0) AS efectivo_mxn,
                   COALESCE(SUM(CASE WHEN fp.forma_pago='efectivo_dolares' THEN fp.monto_pesos ELSE 0 END),0) AS efectivo_usd,
                   COALESCE(SUM(CASE WHEN fp.forma_pago='tarjeta'          THEN fp.monto       ELSE 0 END),0) AS tarjeta,
                   COALESCE(SUM(CASE WHEN fp.forma_pago='transferencia'    THEN fp.monto       ELSE 0 END),0) AS transferencia
            FROM ventas v
            LEFT JOIN ventas_formas_pago fp ON fp.id_venta = v.id AND fp.estado != 2
            WHERE v.estado = 0 AND DATE(v.fecha_alta) BETWEEN ? AND ?
              AND (? IS NULL OR v.id_sucursal = ?)
        ");
        $stmt->execute([$s['desde'], $s['hoy'], $suc, $suc]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getVentasPorDia(): array
    {
        $s = $this->semana(); $suc = $this->suc();
        $ts = strtotime($s['desde']);
        $dias = [];
        for ($i = 0; $i < 7; $i++) {
            $f = date('Y-m-d', $ts + $i * 86400);
            $dias[$f] = ['fecha' => $f, 'total' => 0.0, 'num_ventas' => 0];
        }
        $stmt = $this->db()->prepare("
            SELECT DATE(v.fecha_alta) AS fecha, COUNT(*) AS num_ventas, COALESCE(SUM(v.total),0) AS total
            FROM ventas v
            WHERE v.estado = 0 AND DATE(v.fecha_alta) BETWEEN ? AND ?
              AND (? IS NULL OR v.id_sucursal = ?)
            GROUP BY DATE(v.fecha_alta)
        ");
        $stmt->execute([$s['desde'], $s['hoy'], $suc, $suc]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($dias[$row['fecha']])) {
                $dias[$row['fecha']]['total']      = (float) $row['total'];
                $dias[$row['fecha']]['num_ventas'] = (int)   $row['num_ventas'];
            }
        }
        return array_values($dias);
    }

    public function getResumenCompras(): array
    {
        $s = $this->semana(); $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT COUNT(*) AS num_compras, COALESCE(SUM(total),0) AS total_compras
            FROM compras
            WHERE estado = 0 AND fecha_compra BETWEEN ? AND ?
              AND (? IS NULL OR id_sucursal = ?)
        ");
        $stmt->execute([$s['desde'], $s['hoy'], $suc, $suc]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getResumenEgresos(): array
    {
        $s = $this->semana(); $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT COUNT(*) AS num_egresos, COALESCE(SUM(monto),0) AS total_egresos
            FROM egresos
            WHERE estado = 0 AND fecha_egreso BETWEEN ? AND ?
              AND (? IS NULL OR id_sucursal = ?)
        ");
        $stmt->execute([$s['desde'], $s['hoy'], $suc, $suc]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getResumenInventario(): array
    {
        $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT COUNT(*) AS total_productos,
                   SUM(CASE WHEN p.stock_minimo > 0 AND COALESCE(s.stock_actual,0) <= p.stock_minimo THEN 1 ELSE 0 END) AS bajo_stock
            FROM inventario_productos p
            LEFT JOIN inventario_stock s ON s.id_producto = p.id AND s.id_sucursal = ?
            WHERE p.estado = 0
        ");
        $stmt->execute([$suc]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStockBajo(int $limite = 8): array
    {
        $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT p.id, p.codigo, p.nombre, p.stock_minimo,
                   COALESCE(s.stock_actual, 0) AS stock_actual,
                   u.abreviatura               AS unidad
            FROM inventario_productos p
            LEFT JOIN inventario_stock s ON s.id_producto = p.id AND s.id_sucursal = ?
            LEFT JOIN inventario_unidades_medida u ON u.id = p.id_unidad_medida
            WHERE p.estado = 0 AND p.stock_minimo > 0
              AND COALESCE(s.stock_actual, 0) <= p.stock_minimo
            ORDER BY (COALESCE(s.stock_actual, 0) / p.stock_minimo) ASC
            LIMIT $limite
        ");
        $stmt->execute([$suc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUltimasVentas(int $limite = 7): array
    {
        $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT v.id, v.folio, v.total, v.fecha_alta,
                   TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellidos,''))) AS cajero
            FROM ventas v
            LEFT JOIN admin_usuarios u ON u.id = v.id_usuario
            WHERE v.estado = 0 AND (? IS NULL OR v.id_sucursal = ?)
            ORDER BY v.fecha_alta DESC
            LIMIT $limite
        ");
        $stmt->execute([$suc, $suc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccesosRapidos(): array
    {
        $stmt = $this->db()->prepare("
            SELECT m.clave, m.nombre, m.icono,
                   a.nombre AS area_nombre
            FROM cfg_modulos m
            JOIN cfg_areas a ON a.id = m.id_area
            WHERE m.estado = 0
            ORDER BY a.orden ASC, m.orden ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTurnoActivo(): ?array
    {
        $suc = $this->suc();
        $stmt = $this->db()->prepare("
            SELECT t.id, t.fondo_pesos, t.fondo_dolares, t.fecha_alta,
                   TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellidos,''))) AS cajero,
                   COUNT(v.id)               AS num_ventas,
                   COALESCE(SUM(v.total), 0) AS total_ventas
            FROM ventas_turnos_caja t
            LEFT JOIN admin_usuarios u ON u.id = t.id_usuario
            LEFT JOIN ventas v ON v.id_turno = t.id AND v.estado = 0
            WHERE t.estado = 0 AND (? IS NULL OR t.id_sucursal = ?)
            GROUP BY t.id
            ORDER BY t.fecha_alta DESC
            LIMIT 1
        ");
        $stmt->execute([$suc, $suc]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
