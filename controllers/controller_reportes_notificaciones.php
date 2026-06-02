<?php

class ReportesNotificacionesController {

    public function list() {
        $model = new ReportesNotificacionesModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function navbarList($id_usuario) {
        $model = new ReportesNotificacionesModel();
        $items = $model->getByUser($id_usuario);
        $count = $model->countByUser($id_usuario);
        echo json_encode(['status' => 'ok', 'data' => ['count' => $count, 'items' => $items]]);
    }

    public function delete($id) {
        $model = new ReportesNotificacionesModel();
        $ok    = $model->delete((int) $id);
        echo json_encode([
            'status'  => $ok ? 'ok' : 'error',
            'message' => $ok ? 'Notificación eliminada.' : 'Error al eliminar.',
        ]);
    }
}
