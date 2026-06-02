<?php

class EgresosCategoriasController
{
    private EgresosCategoriasModel $model;

    public function __construct()
    {
        $this->model = new EgresosCategoriasModel();
    }

    public function list(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getAll()]);
    }

    public function listPadres(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getAllPadres()]);
    }

    public function listSubcategorias(mixed $id_padre): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getAllSubcategorias((int) $id_padre)]);
    }

    public function get(mixed $id): void
    {
        $id = (int) $id;
        $row = $this->model->getById($id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Registro no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function getPadres(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getPadres()]);
    }

    public function getPadresManual(): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getPadres(['Compras de inventario'])]);
    }

    public function getSubcategorias(mixed $id_padre): void
    {
        echo json_encode(['status' => 'ok', 'data' => $this->model->getSubcategorias((int) $id_padre)]);
    }

    public function save(array $post): void
    {
        $id       = (int) ($post['id'] ?? 0);
        $nombre   = trim($post['nombre'] ?? '');
        $id_padre = isset($post['id_padre']) && $post['id_padre'] !== '' ? (int) $post['id_padre'] : null;

        if ($nombre === '') {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }

        if ($this->model->nombreExiste($nombre, $id_padre, $id)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe una categoría con ese nombre en el mismo nivel.']);
            return;
        }

        $data = [
            'id'          => $id,
            'nombre'      => $nombre,
            'descripcion' => trim($post['descripcion'] ?? ''),
            'id_padre'    => $id_padre,
            'id_alta'     => $_SESSION['usuario_id'],
        ];

        if ($id > 0) {
            $this->model->update($data);
            echo json_encode(['status' => 'ok', 'message' => 'Categoría actualizada correctamente.']);
        } else {
            $this->model->insert($data);
            echo json_encode(['status' => 'ok', 'message' => 'Categoría creada correctamente.']);
        }
    }

    public function delete(mixed $id): void
    {
        $id = (int) $id;
        if ($this->model->tieneHijos($id)) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar: tiene subcategorías activas.']);
            return;
        }
        if ($this->model->tieneEgresos($id)) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar: está en uso por egresos registrados.']);
            return;
        }
        $this->model->delete($id);
        echo json_encode(['status' => 'ok', 'message' => 'Categoría eliminada correctamente.']);
    }
}
