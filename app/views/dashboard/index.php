<?php
/**
 * Selector de tablero por rol con acceso directo a la Estructura Modular.
 * Variables: $role, $dashboard, $user, $currentDate.
 */
?>
<!-- Accesos directos a los módulos del rol -->
<div class="row g-3 mb-4">
    <?php if ($role === 'ADMINISTRADOR'): ?>
        <div class="col-md-4">
            <a href="<?= e(url('admin/entrevistas')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary p-3 rounded-3 fs-4"><i class="bi bi-calendar2-week-fill"></i></span>
                    <div>
                        <strong class="d-block text-dark">Gestionar Entrevistas</strong>
                        <small class="text-muted">Programar, coordinar y dar seguimiento a aspirantes</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= e(url('admin/usuarios')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success p-3 rounded-3 fs-4"><i class="bi bi-shield-lock-fill"></i></span>
                    <div>
                        <strong class="d-block text-dark">Gestión de Acceso al Sistema</strong>
                        <small class="text-muted">Administrar usuarios, roles (Admin, Profe, Estudiante) y estados</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= e(url('landing')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info-subtle text-info-emphasis p-3 rounded-3 fs-4"><i class="bi bi-globe2"></i></span>
                    <div>
                        <strong class="d-block text-dark">Módulo Público / Visitantes</strong>
                        <small class="text-muted">Ver Landing Page «¿Quiénes somos?» y programas</small>
                    </div>
                </div>
            </a>
        </div>
    <?php elseif ($role === 'PROFESOR'): ?>
        <div class="col-md-4">
            <a href="<?= e(url('profesor/revisar')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary p-3 rounded-3 fs-4"><i class="bi bi-clipboard2-check-fill"></i></span>
                    <div>
                        <strong class="d-block text-dark">Revisar Trabajos</strong>
                        <small class="text-muted">Calificar entregas de alumnos y enviar retroalimentación</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= e(url('profesor/tareas')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success p-3 rounded-3 fs-4"><i class="bi bi-journal-plus"></i></span>
                    <div>
                        <strong class="d-block text-dark">Gestionar Tareas</strong>
                        <small class="text-muted">Crear y asignar tareas por curso y nivel</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= e(url('profesor/reportes')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-warning-subtle text-warning-emphasis p-3 rounded-3 fs-4"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div>
                        <strong class="d-block text-dark">Reportes de Rendimiento</strong>
                        <small class="text-muted">Promedios por grupo y estudiantes en riesgo</small>
                    </div>
                </div>
            </a>
        </div>
    <?php elseif ($role === 'ESTUDIANTE'): ?>
        <div class="col-md-4">
            <a href="<?= e(url('estudiante/trabajos')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary p-3 rounded-3 fs-4"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                    <div>
                        <strong class="d-block text-dark">Entregar Trabajos y Ver Notas</strong>
                        <small class="text-muted">Subir tareas, ver calificaciones y retroalimentación</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= e(url('estudiante/reportes')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success p-3 rounded-3 fs-4"><i class="bi bi-graph-up-arrow"></i></span>
                    <div>
                        <strong class="d-block text-dark">Mis Reportes Académicos</strong>
                        <small class="text-muted">Progreso de curso, notas e historial de asistencias</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= e(url('landing')) ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info-subtle text-info-emphasis p-3 rounded-3 fs-4"><i class="bi bi-globe2"></i></span>
                    <div>
                        <strong class="d-block text-dark">Información de la Academia</strong>
                        <small class="text-muted">Sección «¿Quiénes somos?», niveles y objetivos</small>
                    </div>
                </div>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
if ($role === 'ADMINISTRADOR') {
    require APP_PATH . '/views/dashboard/admin.php';
} elseif ($role === 'PROFESOR') {
    require APP_PATH . '/views/dashboard/professor.php';
} elseif ($role === 'ESTUDIANTE') {
    require APP_PATH . '/views/dashboard/student.php';
} else {
    echo '<div class="empty-state">Rol no reconocido.</div>';
}
