<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0" style="color:var(--color-texto-principal);">Notificaciones</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Historial de notificaciones enviadas a usuarios del sistema</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_notificaciones" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Mensaje</th>
                        <th>Enviada por</th>
                        <th>Destinatarios</th>
                        <th>Fecha de envío</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
