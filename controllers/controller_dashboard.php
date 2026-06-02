<?php

class DashboardController
{
    public function getVentas(): void
    {
        try {
            $model = new DashboardModel();
            echo json_encode(['status' => 'ok', 'data' => [
                'semana'  => $model->semana(),
                'resumen' => $model->getResumenVentas(),
                'por_dia' => $model->getVentasPorDia(),
                'ultimas' => $model->getUltimasVentas(),
            ]]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCompras(): void
    {
        try {
            $model = new DashboardModel();
            echo json_encode(['status' => 'ok', 'data' => $model->getResumenCompras()]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getEgresos(): void
    {
        try {
            $model = new DashboardModel();
            echo json_encode(['status' => 'ok', 'data' => $model->getResumenEgresos()]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getInventario(): void
    {
        try {
            $model = new DashboardModel();
            echo json_encode(['status' => 'ok', 'data' => [
                'resumen'    => $model->getResumenInventario(),
                'stock_bajo' => $model->getStockBajo(),
            ]]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getTurno(): void
    {
        try {
            $model = new DashboardModel();
            echo json_encode(['status' => 'ok', 'data' => $model->getTurnoActivo()]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
