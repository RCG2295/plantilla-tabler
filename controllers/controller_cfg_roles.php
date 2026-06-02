<?php

class CfgRolesController {

    public function list() {
        $model = new CfgRolesModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function select() {
        $model = new CfgRolesModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getSelect()]);
    }

    public function get($id) {
        $model = new CfgRolesModel();
        $rol   = $model->getById((int) $id);
        if (!$rol) {
            echo json_encode(['status' => 'error', 'message' => 'Rol no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $rol]);
    }

    public function save($data) {
        $model       = new CfgRolesModel();
        $id          = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre      = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $es_superadmin = isset($data['es_superadmin']) && $data['es_superadmin'] == '1';
        $estado      = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if ($model->nombreExiste($nombre, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un rol con ese nombre.']);
            return;
        }

        $fields = compact('nombre', 'descripcion', 'es_superadmin', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Rol actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Rol creado correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id) {
        $model = new CfgRolesModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Rol eliminado.' : 'Error al eliminar.']);
    }

    public function getPermisos($id_rol) {
        $model = new CfgRolesModel();
        $areas = $model->getPermisos((int) $id_rol);
        echo json_encode(['status' => 'ok', 'data' => $areas]);
    }

    public function savePermisos($data) {
        $model   = new CfgRolesModel();
        $id_rol  = isset($data['id_rol']) ? (int) $data['id_rol'] : 0;
        $permisos = json_decode($data['permisos'] ?? '[]', true);
        $id_alta  = $_SESSION['usuario_id'] ?? null;

        if (!$id_rol) {
            echo json_encode(['status' => 'error', 'message' => 'Rol no especificado.']);
            return;
        }
        if (!is_array($permisos)) {
            echo json_encode(['status' => 'error', 'message' => 'Datos de permisos inválidos.']);
            return;
        }

        $ok = $model->savePermisos($id_rol, $permisos, $id_alta);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Permisos guardados correctamente.' : 'Error al guardar permisos.']);
    }

}
