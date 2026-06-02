<div class="page-login">
    <div class="login-card card">
        <div class="card-body">

            <!-- Logo -->
            <div class="text-center mb-4">
                <?php $app_url_login = TemplateController::getUrlController(); ?>
                <img src="<?= $app_url_login ?>/views/assets/img/logo.png" alt="e-sol"
                    style="height:48px;width:auto;object-fit:contain;">
            </div>

            <h2 class="text-center fw-bold mb-1" style="color:var(--color-texto-principal);">Bienvenido</h2>
            <p class="text-center text-muted mb-4" style="font-size:0.9rem;">Ingresa tus credenciales para continuar</p>

            <form id="form_login" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="correo@ejemplo.com" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="password">Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="••••••••" required>
                        <button type="button" class="btn btn-outline-secondary" id="btn_ver_password"
                            title="Mostrar/ocultar contraseña">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <circle cx="12" cy="12" r="2"/>
                                <path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btn_ingresar" class="btn btn-primary w-100 fw-semibold"
                    style="background-color:var(--color-primario);border-color:var(--color-primario);">
                    Ingresar
                </button>
            </form>

        </div>
    </div>
</div>

<script>
    document.getElementById('btn_ver_password').addEventListener('click', function () {
        var input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    });
</script>
