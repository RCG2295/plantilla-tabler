<div class="container-xl d-flex flex-column justify-content-center" style="min-height: 60vh;">
    <div class="empty">
        <div class="empty-img">
            <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"
                stroke-width="1" stroke="#c7d2fe" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <circle cx="12" cy="12" r="9"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <p class="empty-title">Página no encontrada</p>
        <p class="empty-subtitle text-muted">
            La ruta que buscas no existe o no tienes permiso para acceder.
        </p>
        <div class="empty-action">
            <a href="<?= TemplateController::getUrlController() ?>/dashboard" class="btn btn-primary">
                Ir al Dashboard
            </a>
        </div>
    </div>
</div>
