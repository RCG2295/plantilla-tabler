<?php

class ReportesNotificacionesModel {

    public function getAll() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT
                n.id,
                n.titulo,
                n.mensaje,
                n.fecha_alta,
                n.estado,
                CONCAT(u.nombre, ' ', u.apellidos) AS enviada_por,
                GROUP_CONCAT(
                    CASE
                        WHEN d.id_usuario IS NULL THEN 'Todos'
                        ELSE CONCAT(du.nombre, ' ', du.apellidos)
                    END
                    ORDER BY d.id SEPARATOR ', '
                ) AS destinatarios
            FROM admin_notificaciones n
            LEFT JOIN admin_usuarios u
                ON n.id_alta = u.id
            LEFT JOIN admin_notificaciones_destinatarios d
                ON n.id = d.id_notificacion AND d.estado != 2
            LEFT JOIN admin_usuarios du
                ON d.id_usuario = du.id
            WHERE n.estado != 2
            GROUP BY n.id, n.titulo, n.mensaje, n.fecha_alta, n.estado, u.nombre, u.apellidos
            ORDER BY n.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser($id_usuario) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT DISTINCT n.id, n.titulo, n.mensaje, n.fecha_alta
            FROM admin_notificaciones n
            JOIN admin_notificaciones_destinatarios d
                ON n.id = d.id_notificacion
            WHERE n.estado != 2
              AND d.estado != 2
              AND (d.id_usuario = ? OR d.id_usuario IS NULL)
            ORDER BY n.fecha_alta DESC
            LIMIT 5
        ");
        $stmt->execute([(int) $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByUser($id_usuario) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT n.id) AS total
            FROM admin_notificaciones n
            JOIN admin_notificaciones_destinatarios d
                ON n.id = d.id_notificacion
            WHERE n.estado != 2
              AND d.estado != 2
              AND (d.id_usuario = ? OR d.id_usuario IS NULL)
        ");
        $stmt->execute([(int) $id_usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public function delete($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare('UPDATE admin_notificaciones SET estado = 2 WHERE id = ?');
        return $stmt->execute([(int) $id]);
    }
}
