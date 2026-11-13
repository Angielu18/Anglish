<?php
/**
 * Pantalla de inicio de sesión.
 * Variables: $oldEmail.
 *
 * El acceso requiere autenticación formal mediante correo y contraseña
 * verificados según el rol asignado en base de datos.
 */
?>
<main class="auth-shell">
    <section class="auth-story" aria-labelledby="authStoryTitle">
        <a class="auth-brand" href="<?= e(url('landing')) ?>">
            <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="" width="52" height="52">
            <span>
                <strong>Anglish</strong>
                <small>Academia de inglés</small>
            </span>
        </a>

        <div class="auth-story-content">
            <p class="eyebrow">Sistema de gestión académica</p>
            <h1 id="authStoryTitle">Aprender inglés,<br><span>con claridad.</span></h1>
            <p class="auth-lead">
                Plataforma modular para el seguimiento académico, entregas de tareas,
                evaluación docente y gestión institucional de la academia.
            </p>

            <ul class="auth-points">
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Programas certificados A1 a C1 (MCER)</li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Control de acceso seguro por rol (RBAC)</li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Entregas, calificaciones y reportes en línea</li>
            </ul>

            <div class="auth-metrics" aria-label="Datos del sistema">
                <div><strong>3</strong><span>módulos por rol</span></div>
                <div><strong>5</strong><span>niveles de inglés</span></div>
                <div><strong>1.0–5.0</strong><span>escala evaluativa</span></div>
            </div>
        </div>

        <p class="auth-credit">Proyecto ADSO · SENA · Angie Valentina Sotelo Ramos</p>
    </section>

    <section class="auth-panel" aria-labelledby="loginTitle">
        <div class="auth-card">
            <?php require APP_PATH . '/views/partials/flash.php'; ?>

            <div class="auth-card-head">
                <span class="auth-kicker"><i class="bi bi-shield-lock" aria-hidden="true"></i> Autenticación formal</span>
                <h2 id="loginTitle">Ingreso al Campus</h2>
                <p>Ingresa tus credenciales institucionales según tu rol (Estudiante, Profesor o Administrador).</p>
            </div>

            <form action="<?= e(url('login')) ?>" method="post" class="auth-form" novalidate>
                <?= csrf_field() ?>

                <div class="form-field">
                    <label for="email" class="form-label">Correo electrónico institucional</label>
                    <div class="input-shell">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($oldEmail ?? '') ?>" placeholder="usuario@anglish.edu.co"
                               autocomplete="username" maxlength="120" required autofocus>
                    </div>
                </div>

                <div class="form-field">
                    <div class="field-label-row">
                        <label for="password" class="form-label">Contraseña</label>
                        <span class="field-hint">Mínimo 8 caracteres</span>
                    </div>
                    <div class="input-shell">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Ingresa tu contraseña" autocomplete="current-password"
                               minlength="8" maxlength="72" required>
                        <button class="password-toggle" type="button" data-password-toggle="password"
                                aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    Iniciar sesión <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>

                <div class="auth-help">
                    <span><i class="bi bi-shield-check" aria-hidden="true"></i> Acceso protegido con validación RBAC</span>
                </div>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <p class="text-muted small mb-2">¿Aún no eres parte de Anglish o deseas conocer nuestros programas?</p>
                <a href="<?= e(url('landing')) ?>" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-globe2 me-1" aria-hidden="true"></i> Explorar portal público como Visitante
                </a>
            </div>
        </div>
    </section>
</main>
