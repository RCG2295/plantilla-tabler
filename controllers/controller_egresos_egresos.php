<?php

class EgresosEgresosController
{
    private EgresosEgresosModel $model;

    public function __construct()
    {
        $this->model = new EgresosEgresosModel();
    }

    public function listActivos(): void
    {
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-30 days'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))  $hasta = date('Y-m-d');
        echo json_encode(['status' => 'ok', 'data' => $this->model->getByEstadoConFechas(0, $desde, $hasta)]);
    }

    public function listCancelados(): void
    {
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-30 days'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))  $hasta = date('Y-m-d');
        echo json_encode(['status' => 'ok', 'data' => $this->model->getByEstadoConFechas(1, $desde, $hasta)]);
    }

    public function get(mixed $id): void
    {
        $row = $this->model->getById((int) $id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Egreso no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save(array $post): void
    {
        $id_categoria = (int) ($post['id_categoria'] ?? 0);
        $monto        = (float) ($post['monto'] ?? 0);
        $concepto     = trim($post['concepto'] ?? '');
        $fecha_egreso = trim($post['fecha_egreso'] ?? '');
        $metodo_pago  = trim($post['metodo_pago'] ?? 'efectivo');

        if (!$id_categoria) {
            echo json_encode(['status' => 'error', 'message' => 'La categoría es requerida.']);
            return;
        }
        if ($monto <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'El monto debe ser mayor a 0.']);
            return;
        }
        if ($concepto === '') {
            echo json_encode(['status' => 'error', 'message' => 'El concepto es requerido.']);
            return;
        }
        if ($fecha_egreso === '') {
            echo json_encode(['status' => 'error', 'message' => 'La fecha es requerida.']);
            return;
        }

        $id_subcategoria = isset($post['id_subcategoria']) && $post['id_subcategoria'] !== '' ? (int) $post['id_subcategoria'] : null;
        $referencia      = $metodo_pago !== 'efectivo' ? (trim($post['referencia'] ?? '') ?: null) : null;

        $archivo = null;
        if (!empty($_FILES['archivo']['name'])) {
            $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            $mime    = mime_content_type($_FILES['archivo']['tmp_name']);
            if (!in_array($mime, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Solo se permiten archivos PDF, JPG, PNG o WEBP.']);
                return;
            }
            if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'El archivo no debe superar 5 MB.']);
                return;
            }
            $ext     = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            $archivo = 'egr_' . uniqid('', true) . '.' . $ext;
            $destino = __DIR__ . '/../views/uploads/egresos_egresos/' . $archivo;
            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al guardar el archivo adjunto.']);
                return;
            }
        }

        $data = [
            'id_compra'      => null,
            'id_categoria'   => $id_categoria,
            'id_subcategoria'=> $id_subcategoria,
            'concepto'       => $concepto,
            'fecha_egreso'   => $fecha_egreso,
            'monto'          => $monto,
            'metodo_pago'    => $metodo_pago,
            'referencia'     => $referencia,
            'notas'          => trim($post['notas'] ?? '') ?: null,
            'archivo'        => $archivo,
            'id_alta'        => $_SESSION['usuario_id'],
        ];

        $this->model->insert($data);
        echo json_encode(['status' => 'ok', 'message' => 'Egreso registrado correctamente.']);
    }

    public function cancelar(mixed $id): void
    {
        $id = (int) $id;
        $egreso = $this->model->getById($id);
        if (!$egreso) {
            echo json_encode(['status' => 'error', 'message' => 'Egreso no encontrado.']);
            return;
        }
        if ((int) $egreso['estado'] !== 0) {
            echo json_encode(['status' => 'error', 'message' => 'El egreso ya está cancelado.']);
            return;
        }
        $this->model->cancelar($id);
        echo json_encode(['status' => 'ok', 'message' => 'Egreso cancelado correctamente.']);
    }
}
