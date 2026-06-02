<?php

class CfgAreasController {

    public function list() {
        $model = new CfgAreasModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function get($id) {
        $model = new CfgAreasModel();
        $area  = $model->getById((int) $id);
        if (!$area) {
            echo json_encode(['status' => 'error', 'message' => 'Área no encontrada.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $area]);
    }

    public function save($data) {
        $model  = new CfgAreasModel();
        $id     = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre = trim($data['nombre'] ?? '');
        $icono  = trim($data['icono'] ?? '');
        $orden  = isset($data['orden']) ? (int) $data['orden'] : 0;
        $estado = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if (!$icono) {
            echo json_encode(['status' => 'error', 'message' => 'El ícono es requerido.']);
            return;
        }
        if ($model->nombreExiste($nombre, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un área con ese nombre.']);
            return;
        }

        $fields = compact('nombre', 'icono', 'orden', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Área actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Área creada correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id) {
        $model = new CfgAreasModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Área eliminada.' : 'Error al eliminar.']);
    }

}
