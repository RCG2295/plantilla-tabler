<?php

class VentasCajaController
{
    private VentasCajaModel $model;

    public function __construct()
    {
        $this->model = new VentasCajaModel();
    }

    public function getTurnoActivo(): void
    {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        $turno = $this->model->getTurnoActivo($id_usuario);
        echo json_encode(['status' => 'ok', 'data' => $turno]);
    }

    public function getAll(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getAll()]);
    }

    public function getDenominacionesFijas(): void
    {
        echo json_encode(['status' => 'ok', 'data' => VentasCajaModel::getDenominacionesFijas()]);
    }

    public function getResumen(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getResumenTurno($id_turno)]);
    }

    public function getCorte(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getCorte($id_turno)]);
    }

    public function getDenominaciones(int $id_turno, string $momento): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getDenominaciones($id_turno, $momento)]);
    }

    public function getMovimientos(int $id_turno): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getMovimientos($id_turno)]);
    }

    public function iniciar(array $post): void
    {
        $id_usuario  = $_SESSION['usuario_id'] ?? null;
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;

        $activo = $this->model->getTurnoActivo($id_usuario);
        if ($activo) {
            echo json_encode(['status' => 'error', 'message' => 'Ya tienes un turno activo.']);
            return;
        }

        $fondo_pesos   = (float) ($post['fondo_pesos']   ?? 0);
        $fondo_dolares = (float) ($post['fondo_dolares'] ?? 0);

        $id_turno = $this->model->iniciar([
            'id_usuario'    => $id_usuario,
            'id_sucursal'   => $id_sucursal,
            'fondo_pesos'   => $fondo_pesos,
            'fondo_dolares' => $fondo_dolares,
            'id_alta'       => $id_usuario,
        ]);

        if (!empty($post['denominaciones']) && is_array($post['denominaciones'])) {
            $dens = [];
            foreach ($post['denominaciones'] as $d) {
                if ((int) ($d['cantidad'] ?? 0) <= 0) continue;
                $dens[] = [
                    'moneda'       => $d['moneda'],
                    'tipo'         => $d['tipo'],
                    'denominacion' => (float) $d['denominacion'],
                    'cantidad'     => (int) $d['cantidad'],
                    'id_alta'      => $id_usuario,
                ];
            }
            if ($dens) $this->model->insertDenominaciones($id_turno, 'apertura', $dens);
        }

        echo json_encode(['status' => 'ok', 'message' => 'Turno iniciado correctamente.', 'id_turno' => $id_turno]);
    }

    public function cerrar(array $post): void
    {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        $id_turno   = (int) ($post['id_turno'] ?? 0);
        if (!$id_turno) {
            echo json_encode(['status' => 'error', 'message' => 'Turno no especificado.']);
            return;
        }

        if (!empty($post['denominaciones']) && is_array($post['denominaciones'])) {
            $dens = [];
            foreach ($post['denominaciones'] as $d) {
                if ((int) ($d['cantidad'] ?? 0) <= 0) continue;
                $dens[] = [
                    'moneda'       => $d['moneda'],
                    'tipo'         => $d['tipo'],
                    'denominacion' => (float) $d['denominacion'],
                    'cantidad'     => (int) $d['cantidad'],
                    'id_alta'      => $id_usuario,
                ];
            }
            if ($dens) $this->model->insertDenominaciones($id_turno, 'cierre', $dens);
        }

        $resumen = $this->model->getResumenTurno($id_turno);

        $tcModel     = new VentasTipoCambioModel();
        $tipo_cambio = $tcModel->getVigente();
        $tc_valor    = $tipo_cambio ? (float) $tipo_cambio['valor'] : 0;

        $declarado_pesos   = (float) ($post['declarado_pesos']   ?? 0);
        $declarado_dolares = (float) ($post['declarado_dolares'] ?? 0);

        $this->model->insertCorte([
            'id_turno'                   => $id_turno,
            'total_ventas'               => $resumen['total_ventas'],
            'total_efectivo_pesos'       => $resumen['efectivo_pesos'],
            'total_efectivo_dolares'     => $resumen['efectivo_dolares'],
            'total_tarjeta'              => $resumen['tarjeta'],
            'total_transferencia'        => $resumen['transferencia'],
            'efectivo_esperado_pesos'    => $resumen['efectivo_esperado_pesos'],
            'efectivo_declarado_pesos'   => $declarado_pesos,
            'diferencia_pesos'           => $declarado_pesos - $resumen['efectivo_esperado_pesos'],
            'efectivo_esperado_dolares'  => $resumen['efectivo_esperado_dolares'],
            'efectivo_declarado_dolares' => $declarado_dolares,
            'diferencia_dolares'         => $declarado_dolares - $resumen['efectivo_esperado_dolares'],
            'tipo_cambio_usado'          => $tc_valor,
            'id_alta'                    => $id_usuario,
        ]);

        $this->model->cerrar($id_turno);
        echo json_encode(['status' => 'ok', 'message' => 'Turno cerrado correctamente.']);
    }

    public function insertMovimiento(array $post): void
    {
        $id_usuario  = $_SESSION['usuario_id'] ?? null;
        $id_turno    = (int) ($post['id_turno'] ?? 0);
        $tipo        = in_array($post['tipo'] ?? '', ['retiro', 'ingreso']) ? $post['tipo'] : null;
        $moneda      = in_array($post['moneda'] ?? '', ['pesos', 'dolares']) ? $post['moneda'] : null;
        $monto       = (float) ($post['monto'] ?? 0);
        $descripcion = trim($post['descripcion'] ?? '') ?: null;

        if (!$id_turno || !$tipo || !$moneda || $monto <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
            return;
        }

        if ($tipo === 'retiro') {
            $resumen    = $this->model->getResumenTurno($id_turno);
            $disponible = $moneda === 'pesos'
                ? (float) $resumen['efectivo_esperado_pesos']
                : (float) $resumen['efectivo_esperado_dolares'];
            $label = $moneda === 'pesos' ? 'MXN' : 'USD';
            if ($monto > $disponible) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Fondos insuficientes. Disponible en caja: $' . number_format($disponible, 2) . ' ' . $label . '.',
                ]);
                return;
            }
        }

        $this->model->insertMovimiento([
            'id_turno'    => $id_turno,
            'tipo'        => $tipo,
            'moneda'      => $moneda,
            'monto'       => $monto,
            'descripcion' => $descripcion,
            'id_alta'     => $id_usuario,
        ]);
        echo json_encode(['status' => 'ok', 'message' => ucfirst($tipo) . ' registrado correctamente.']);
    }
}
