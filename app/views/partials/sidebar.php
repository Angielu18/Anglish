<?php
/**
 * Menú lateral modularizado por rol.
 *
 * Estructura modular:
 *  - Módulo de Administrador: Inicio, Gestionar entrevistas, Gestión de acceso al sistema.
 *  - Módulo de Profesores: Inicio, Revisar trabajos, Gestionar tareas, Reportes.
 *  - Módulo de Estudiantes: Inicio, Entregar trabajos y ver notas, Reportes.
 *  - Enlace al Módulo Público / Visitantes.
 */
$roleName = (string) ($role ?? ($user['rol'] ?? 'ESTUDIANTE'));
$nombre = (string) ($user['nombre_completo'] ?? 'Usuario');
$iniciales = iniciales($nombre);

$menu = [
    'ADMINISTRADOR' => [
        ['route' => 'dashboard', 'label' => 'Inicio (Dashboard)', 'icon' => 'bi-grid-1x2-fill', 'enabled' => true],
        ['route' => 'admin/entrevistas', 'label' => 'Gestionar entrevistas', 'icon' => 'bi-calendar2-week-fill', 'enabled' => true],
        ['route' => 'admin/usuarios', 'label' => 'Gestión de acceso', 'icon' => 'bi-shield-lock-fill', 'enabled' => true],
    ],
    'PROFESOR' => [
        ['route' => 'dashboard', 'label' => 'Inicio (Dashboard)', 'icon' => 'bi-grid-1x2-fill', 'enabled' => true],
        ['route' => 'profesor/revisar', 'label' => 'Revisar trabajos', 'icon' => 'bi-clipboard2-check-fill', 'enabled' => true],
        ['route' => 'profesor/tareas', 'label' => 'Gestionar tareas', 'icon' => 'bi-journal-plus', 'enabled' => true],
        ['route' => 'profesor/reportes', 'label' => 'Reportes', 'icon' => 'bi-bar-chart-line-fill', 'enabled' => true],
    ],
    'ESTUDIANTE' => [
        ['route' => 'dashboard', 'label' => 'Inicio (Dashboard)', 'icon' => 'bi-grid-1x2-fill', 'enabled' => true],
        ['route' => 'estudiante/trabajos', 'label' => 'Entregar trabajos y notas', 'icon' => 'bi-cloud-arrow-up-fill', 'enabled' => true],
        ['route' => 'estudiante/reportes', 'label' => 'Reportes', 'icon' => 'bi-graph-up-arrow', 'enabled' => true],
    ],
];

$moduleTitle = [
    'ADMINISTRADOR' => 'Módulo Administrador',
    'PROFESOR'      => 'Módulo de Profesores',
    'ESTUDIANTE'    => 'Módulo de Estudiantes',
][$roleName] ?? 'Módulo Académico';

$items = $menu[$roleName] ?? $menu['ESTUDIANTE'];
?>
<aside class="app-sidebar" id="appSidebar" aria-label="Menú principal">
    <div class="sidebar-brand">
        <a href="<?= e(url('dashboard')) ?>" class="brand-link">
            <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="" width="42" height="42" class="brand-mark">
            <span>
                <strong>Anglish</strong>
                <small>Academia de inglés</small>
            </span>
        </a>
    </div>

    <div class="sidebar-role">
        <span class="role-dot" aria-hidden="true"></span>
        <span><?= e(rol_etiqueta($roleName)) ?></span>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-section"><?= e($moduleTitle) ?></p>

        <?php foreach ($items as $item): ?>
            <a class="nav-link <?= ruta_activa($item['route']) ? 'active' : '' ?>"
               href="<?= e(url($item['route'])) ?>"
               <?= ruta_activa($item['route']) ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= e($item['icon']) ?>" aria-hidden="true"></i>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>

        <p class="nav-section nav-section-spaced">Sitio institucional</p>
        <a class="nav-link" href="<?= e(url('landing')) ?>">
            <i class="bi bi-globe2" aria-hidden="true"></i>
            <span>Portal Público / Visitante</span>
        </a>
    </nav>

    <div class="sidebar-foot">
        <div class="sidebar-user">
            <span class="avatar avatar-sm" aria-hidden="true"><?= e($iniciales) ?></span>
            <span class="sidebar-user-text">
                <strong><?= e($nombre) ?></strong>
                <small><?= e((string) ($user['email'] ?? '')) ?></small>
            </span>
        </div>
        <p class="sidebar-version">Anglish v<?= e((string) config('app.version', '3.0.0')) ?></p>
    </div>
</aside>
