<?php

class InventarioCategoriasController
{
    public function list()
    {
        $model = new InventarioCategoriasModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function listByPadre($id_padre)
    {
        $model = new InventarioCategoriasModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getByPadre((int) $id_padre)]);
    }

    public function padres()
    {
        $model = new InventarioCategoriasModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getPadres()]);
    }

    public function get($id)
    {
        $model = new InventarioCategoriasModel();
        $row   = $model->getById((int) $id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Categoría no encontrada.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save($data)
    {
        $model       = new InventarioCategoriasModel();
        $id          = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre      = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $id_padre    = isset($data['id_padre']) && (int) $data['id_padre'] > 0 ? (int) $data['id_padre'] : null;
        $estado      = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }

        // Prevent a parent from becoming its own child
        if ($id && $id_padre === $id) {
            echo json_encode(['status' => 'error', 'message' => 'Una categoría no puede ser su propio padre.']);
            return;
        }

        if ($model->nombreExiste($nombre, $id_padre, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe una categoría con ese nombre en el mismo nivel.']);
            return;
        }

        $fields = compact('nombre', 'descripcion', 'id_padre', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Categoría actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Categoría creada correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id)
    {
        $model = new InventarioCategoriasModel();
        $id    = (int) $id;

        if ($model->tieneHijos($id)) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar: la categoría tiene subcategorías activas.']);
            return;
        }

        $ok = $model->delete($id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Categoría eliminada.' : 'Error al eliminar.']);
    }
}
