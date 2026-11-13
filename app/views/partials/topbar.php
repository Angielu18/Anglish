<?php
/**
 * Barra superior: navegación móvil, título, enlaces rápidos por módulo y menú de usuario.
 */
$nombre = (string) ($user['nombre_completo'] ?? 'Usuario');
$roleName = (string) ($role ?? ($user['rol'] ?? 'ESTUDIANTE'));
$iniciales = iniciales($nombre);
?>
<header class="app-topbar">
    <div class="topbar-left">
        <button class="btn btn-icon topbar-toggle" type="button" id="sidebarToggle"
                aria-label="Abrir menú" aria-controls="appSidebar" aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <div class="topbar-title">
            <h1><?= e((string) ($title ?? 'Panel')) ?></h1>
            <?php if (!empty($subtitle)): ?>
                <p><?= e((string) $subtitle) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="topbar-right">
        <a class="btn btn-outline-primary btn-sm d-none d-md-inline-flex align-items-center gap-1" href="<?= e(url('landing')) ?>">
            <i class="bi bi-globe2" aria-hidden="true"></i> Sitio Público
        </a>

        <a class="btn btn-icon topbar-bell" href="<?= e(url('dashboard')) ?>#alertas"
           aria-label="Ver alertas" title="Alertas">
            <i class="bi bi-bell" aria-hidden="true"></i>
            <span class="notification-dot" aria-hidden="true"></span>
        </a>

        <div class="dropdown">
            <button class="user-menu" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Abrir menú de usuario">
                <span class="avatar" aria-hidden="true"><?= e($iniciales) ?></span>
                <span class="user-menu-text">
                    <strong><?= e($nombre) ?></strong>
                    <small><?= e(rol_etiqueta($roleName)) ?></small>
                </span>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                <li class="dropdown-header">
                    <strong><?= e($nombre) ?></strong>
                    <span><?= e((string) ($user['email'] ?? '')) ?></span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= e(url('dashboard')) ?>">
                        <i class="bi bi-grid-1x2" aria-hidden="true"></i> Inicio (Dashboard)
                    </a>
                </li>
                <?php if ($roleName === 'ADMINISTRADOR'): ?>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('admin/entrevistas')) ?>">
                            <i class="bi bi-calendar2-week" aria-hidden="true"></i> Gestionar entrevistas
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('admin/usuarios')) ?>">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i> Gestión de acceso
                        </a>
                    </li>
                <?php elseif ($roleName === 'PROFESOR'): ?>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('profesor/revisar')) ?>">
                            <i class="bi bi-clipboard2-check" aria-hidden="true"></i> Revisar trabajos
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('profesor/tareas')) ?>">
                            <i class="bi bi-journal-plus" aria-hidden="true"></i> Gestionar tareas
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('profesor/reportes')) ?>">
                            <i class="bi bi-bar-chart-line" aria-hidden="true"></i> Reportes
                        </a>
                    </li>
                <?php elseif ($roleName === 'ESTUDIANTE'): ?>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('estudiante/trabajos')) ?>">
                            <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Entregar trabajos y notas
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= e(url('estudiante/reportes')) ?>">
                            <i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Mis reportes
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a class="dropdown-item" href="<?= e(url('landing')) ?>">
                        <i class="bi bi-globe2" aria-hidden="true"></i> Portal Público / Visitantes
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?= e(url('logout')) ?>" method="post" class="logout-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
