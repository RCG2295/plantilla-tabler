<?php

class VentasCajaModel
{
    // ── Denominaciones fijas ─────────────────────────────────────────────────
    public static function getDenominacionesFijas(): array
    {
        return [
            'pesos' => [
                'billetes' => [1000, 500, 200, 100, 50, 20],
                'monedas'  => [20, 10, 5, 2, 1, 0.50],
            ],
            'dolares' => [
                'billetes' => [100, 50, 20, 10, 5, 2, 1],
                'monedas'  => [],
            ],
        ];
    }

    // ── Turno ────────────────────────────────────────────────────────────────

    public function getTurnoActivo($id_usuario)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT t.*, u.nombre AS usuario_nombre, u.apellidos AS usuario_apellidos,
                   s.nombre AS sucursal_nombre
            FROM ventas_turnos_caja t
            LEFT JOIN admin_usuarios u ON u.id = t.id_usuario
            LEFT JOIN admin_sucursales s ON s.id = t.id_sucursal
            WHERE t.id_usuario = ? AND t.estado = 0
              AND (t.id_sucursal = ? OR ? IS NULL)
            ORDER BY t.fecha_alta DESC
            LIMIT 1
        ");
        $stmt->execute([$id_usuario, $id_sucursal, $id_sucursal]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getById(int $id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT t.*, u.nombre AS usuario_nombre, u.apellidos AS usuario_apellidos,
                   s.nombre AS sucursal_nombre
            FROM ventas_turnos_caja t
            LEFT JOIN admin_usuarios u ON u.id = t.id_usuario
            LEFT JOIN admin_sucursales s ON s.id = t.id_sucursal
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAll()
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT t.id, t.fondo_pesos, t.fondo_dolares, t.estado,
                   t.fecha_alta, t.fecha_cierre,
                   u.nombre AS usuario_nombre, u.apellidos AS usuario_apellidos,
                   s.nombre AS sucursal_nombre
            FROM ventas_turnos_caja t
            LEFT JOIN admin_usuarios u ON u.id = t.id_usuario
            LEFT JOIN admin_sucursales s ON s.id = t.id_sucursal
            WHERE t.estado != 2
              AND (t.id_sucursal = ? OR ? IS NULL)
            ORDER BY t.fecha_alta DESC
            LIMIT 200
        ");
        $stmt->execute([$id_sucursal, $id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByRango(string $desde, string $hasta): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT t.id, t.fondo_pesos, t.fondo_dolares, t.estado,
                   t.fecha_alta, t.fecha_cierre,
                   u.nombre AS usuario_nombre, u.apellidos AS usuario_apellidos,
                   s.nombre AS sucursal_nombre
            FROM ventas_turnos_caja t
            LEFT JOIN admin_usuarios u ON u.id = t.id_usuario
            LEFT JOIN admin_sucursales s ON s.id = t.id_sucursal
            WHERE t.estado != 2
              AND DATE(t.fecha_alta) BETWEEN ? AND ?
              AND (t.id_sucursal = ? OR ? IS NULL)
            ORDER BY t.fecha_alta DESC
        ");
        $stmt->execute([$desde, $hasta, $id_sucursal, $id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function iniciar($data): int
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO ventas_turnos_caja
                (id_usuario, id_sucursal, fondo_pesos, fondo_dolares, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, 0, ?, NOW())
        ");
        $stmt->execute([
            $data['id_usuario'],
            $data['id_sucursal'] ?? null,
            $data['fondo_pesos']   ?? 0,
            $data['fondo_dolares'] ?? 0,
            $data['id_alta']       ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    public function cerrar(int $id_turno): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE ventas_turnos_caja SET estado = 1, fecha_cierre = NOW() WHERE id = ?");
        return $stmt->execute([$id_turno]);
    }

    // ── Denominaciones ───────────────────────────────────────────────────────

    public function insertDenominaciones(int $id_turno, string $momento, array $denominaciones): void
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO ventas_turnos_denominaciones
                (id_turno, momento, moneda, tipo, denominacion, cantidad, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, 0, ?, NOW())
        ");
        foreach ($denominaciones as $d) {
            $stmt->execute([
                $id_turno,
                $momento,
                $d['moneda'],
                $d['tipo'],
                $d['denominacion'],
                (int) $d['cantidad'],
                $d['id_alta'] ?? null,
            ]);
        }
    }

    public function getDenominaciones(int $id_turno, string $momento): array
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT moneda, tipo, denominacion, cantidad
            FROM ventas_turnos_denominaciones
            WHERE id_turno = ? AND momento = ? AND estado != 2
            ORDER BY moneda, tipo DESC, denominacion DESC
        ");
        $stmt->execute([$id_turno, $momento]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Movimientos (retiros/ingresos) ───────────────────────────────────────

    public function insertMovimiento($data): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO ventas_caja_movimientos
                (id_turno, tipo, moneda, monto, descripcion, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, 0, ?, NOW())
        ");
        return $stmt->execute([
            $data['id_turno'],
            $data['tipo'],
            $data['moneda'],
            $data['monto'],
            $data['descripcion'] ?: null,
            $data['id_alta'] ?? null,
        ]);
    }

    public function getMovimientos(int $id_turno): array
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT m.id, m.tipo, m.moneda, m.monto, m.descripcion, m.fecha_alta,
                   u.nombre AS usuario
            FROM ventas_caja_movimientos m
            LEFT JOIN admin_usuarios u ON u.id = m.id_alta
            WHERE m.id_turno = ? AND m.estado != 2
            ORDER BY m.fecha_alta DESC
        ");
        $stmt->execute([$id_turno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Corte ────────────────────────────────────────────────────────────────

    public function getResumenTurno(int $id_turno): array
    {
        $db = Connection::connect();

        // Totals from sales
        $stmt = $db->prepare("
            SELECT
                COUNT(v.id) AS num_ventas,
                COALESCE(SUM(CASE WHEN v.estado = 0 THEN v.total ELSE 0 END), 0) AS total_ventas,
                COALESCE(SUM(CASE WHEN fp.forma_pago = 'efectivo_pesos'   AND v.estado = 0 THEN fp.monto ELSE 0 END), 0) AS efectivo_pesos,
                COALESCE(SUM(CASE WHEN fp.forma_pago = 'efectivo_dolares' AND v.estado = 0 THEN fp.monto ELSE 0 END), 0) AS efectivo_dolares,
                COALESCE(SUM(CASE WHEN fp.forma_pago = 'tarjeta'          AND v.estado = 0 THEN fp.monto ELSE 0 END), 0) AS tarjeta,
                COALESCE(SUM(CASE WHEN fp.forma_pago = 'transferencia'    AND v.estado = 0 THEN fp.monto ELSE 0 END), 0) AS transferencia
            FROM ventas v
            LEFT JOIN ventas_formas_pago fp ON fp.id_venta = v.id AND fp.estado != 2
            WHERE v.id_turno = ?
        ");
        $stmt->execute([$id_turno]);
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);

        // Totals from cash movements
        $stmt = $db->prepare("
            SELECT tipo, moneda, COALESCE(SUM(monto), 0) AS total
            FROM ventas_caja_movimientos
            WHERE id_turno = ? AND estado != 2
            GROUP BY tipo, moneda
        ");
        $stmt->execute([$id_turno]);
        $movs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mov = ['retiro_pesos' => 0, 'retiro_dolares' => 0, 'ingreso_pesos' => 0, 'ingreso_dolares' => 0];
        foreach ($movs as $m) {
            $key = $m['tipo'] . '_' . $m['moneda'];
            $mov[$key] = (float) $m['total'];
        }

        // Turno fondo
        $stmt = $db->prepare("SELECT fondo_pesos, fondo_dolares FROM ventas_turnos_caja WHERE id = ?");
        $stmt->execute([$id_turno]);
        $turno = $stmt->fetch(PDO::FETCH_ASSOC);

        $fondo_pesos   = (float) ($turno['fondo_pesos']   ?? 0);
        $fondo_dolares = (float) ($turno['fondo_dolares'] ?? 0);

        return [
            'num_ventas'               => (int)   $ventas['num_ventas'],
            'total_ventas'             => (float)  $ventas['total_ventas'],
            'efectivo_pesos'           => (float)  $ventas['efectivo_pesos'],
            'efectivo_dolares'         => (float)  $ventas['efectivo_dolares'],
            'tarjeta'                  => (float)  $ventas['tarjeta'],
            'transferencia'            => (float)  $ventas['transferencia'],
            'fondo_pesos'              => $fondo_pesos,
            'fondo_dolares'            => $fondo_dolares,
            'ingresos_pesos'           => $mov['ingreso_pesos'],
            'ingresos_dolares'         => $mov['ingreso_dolares'],
            'retiros_pesos'            => $mov['retiro_pesos'],
            'retiros_dolares'          => $mov['retiro_dolares'],
            'efectivo_esperado_pesos'  => $fondo_pesos   + (float)$ventas['efectivo_pesos']   + $mov['ingreso_pesos']   - $mov['retiro_pesos'],
            'efectivo_esperado_dolares'=> $fondo_dolares + (float)$ventas['efectivo_dolares'] + $mov['ingreso_dolares'] - $mov['retiro_dolares'],
        ];
    }

    public function insertCorte($data): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO ventas_cortes
                (id_turno, total_ventas, total_efectivo_pesos, total_efectivo_dolares,
                 total_tarjeta, total_transferencia,
                 efectivo_esperado_pesos, efectivo_declarado_pesos, diferencia_pesos,
                 efectivo_esperado_dolares, efectivo_declarado_dolares, diferencia_dolares,
                 tipo_cambio_usado, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())
        ");
        return $stmt->execute([
            $data['id_turno'],
            $data['total_ventas'],
            $data['total_efectivo_pesos'],
            $data['total_efectivo_dolares'],
            $data['total_tarjeta'],
            $data['total_transferencia'],
            $data['efectivo_esperado_pesos'],
            $data['efectivo_declarado_pesos'],
            $data['diferencia_pesos'],
            $data['efectivo_esperado_dolares'],
            $data['efectivo_declarado_dolares'],
            $data['diferencia_dolares'],
            $data['tipo_cambio_usado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function getCorte(int $id_turno)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT * FROM ventas_cortes WHERE id_turno = ? AND estado != 2 LIMIT 1");
        $stmt->execute([$id_turno]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
