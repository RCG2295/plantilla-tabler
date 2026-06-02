<?php

use Phinx\Seed\AbstractSeed;

class NotificacionesSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UsuarioAdminSeed'];
    }

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $d5  = date('Y-m-d H:i:s', strtotime('-5 days'));
        $d3  = date('Y-m-d H:i:s', strtotime('-3 days'));
        $d2  = date('Y-m-d H:i:s', strtotime('-2 days'));
        $d1  = date('Y-m-d H:i:s', strtotime('-1 day'));

        $notificaciones = [
            [
                'titulo'      => 'Bienvenido al sistema',
                'mensaje'     => 'El sistema ha sido configurado exitosamente. Puedes comenzar a gestionar usuarios y módulos desde el panel de administración.',
                'fecha'       => $d5,
                'destinatario' => null,
            ],
            [
                'titulo'      => 'Mantenimiento programado',
                'mensaje'     => 'Se realizará mantenimiento el próximo lunes de 2:00 a 4:00 AM. El sistema estará temporalmente fuera de servicio durante ese período.',
                'fecha'       => $d3,
                'destinatario' => null,
            ],
            [
                'titulo'      => 'Actualización de perfil pendiente',
                'mensaje'     => 'Tu perfil de usuario requiere información adicional. Por favor, completa los datos de contacto para continuar usando el sistema.',
                'fecha'       => $d2,
                'destinatario' => 1,
            ],
            [
                'titulo'      => 'Revisión de permisos completada',
                'mensaje'     => 'Se ha realizado una revisión de los permisos de acceso. Tu cuenta mantiene acceso total como administrador del sistema.',
                'fecha'       => $d1,
                'destinatario' => 1,
            ],
            [
                'titulo'      => 'Módulo de notificaciones activo',
                'mensaje'     => 'El módulo de notificaciones ha sido habilitado correctamente. Ya puedes gestionar y enviar notificaciones a los usuarios del sistema.',
                'fecha'       => $now,
                'destinatario' => null,
            ],
        ];

        foreach ($notificaciones as $n) {
            $titulo  = addslashes($n['titulo']);
            $mensaje = addslashes($n['mensaje']);
            $fecha   = $n['fecha'];

            $this->execute("
                INSERT INTO admin_notificaciones (titulo, mensaje, id_alta, estado, fecha_alta)
                VALUES ('$titulo', '$mensaje', 1, 0, '$fecha')
            ");

            $rows = $this->fetchAll('SELECT MAX(id) AS id FROM admin_notificaciones');
            $nid  = (int) $rows[0]['id'];
            $uid  = $n['destinatario'] === null ? 'NULL' : (int) $n['destinatario'];

            $this->execute("
                INSERT INTO admin_notificaciones_destinatarios (id_notificacion, id_usuario, id_alta, estado, fecha_alta)
                VALUES ($nid, $uid, 1, 0, '$fecha')
            ");
        }
    }
}
