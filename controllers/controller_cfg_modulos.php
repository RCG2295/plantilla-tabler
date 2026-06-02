<?php

class CfgModulosController {

    public function list() {
        $model = new CfgModulosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function get($id) {
        $model   = new CfgModulosModel();
        $modulo  = $model->getById((int) $id);
        if (!$modulo) {
            echo json_encode(['status' => 'error', 'message' => 'Módulo no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $modulo]);
    }

    public function save($data) {
        $model   = new CfgModulosModel();
        $id      = isset($data['id']) ? (int) $data['id'] : 0;
        $id_area = isset($data['id_area']) ? (int) $data['id_area'] : 0;
        $clave   = trim($data['clave'] ?? '');
        $nombre  = trim($data['nombre'] ?? '');
        $icono   = trim($data['icono'] ?? '');
        $orden   = isset($data['orden']) ? (int) $data['orden'] : 0;
        $estado  = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$id_area) {
            echo json_encode(['status' => 'error', 'message' => 'Selecciona un área.']);
            return;
        }
        if (!$clave) {
            echo json_encode(['status' => 'error', 'message' => 'La clave es requerida.']);
            return;
        }
        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if ($model->claveExiste($clave, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un módulo con esa clave.']);
            return;
        }

        $fields = compact('id_area', 'clave', 'nombre', 'icono', 'orden', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Módulo actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Módulo creado correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id) {
        $model = new CfgModulosModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Módulo eliminado.' : 'Error al eliminar.']);
    }

}
