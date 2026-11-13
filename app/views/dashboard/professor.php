<?php
/**
 * Tablero del Profesor.
 * Variables: $dashboard, $user, $currentDate.
 */
$d = $dashboard;
$s = $d['stats'];
$nombre = (string) ($user['nombres'] ?? 'Profesor');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">Panel docente</span>
        <h2>Hola, <?= e($nombre) ?></h2>
        <p>Estos son tus cursos, próximas sesiones y estudiantes que requieren apoyo.</p>
    </div>
    <div class="page-date">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        <span><?= e($currentDate) ?></span>
    </div>
</div>

<div class="row g-3 stat-grid">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-teal">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Cursos activos</span>
                <strong><?= e((string) $s['cursos']) ?></strong>
                <small>Asignados a tu perfil</small>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-blue">
            <div class="stat-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Estudiantes</span>
                <strong><?= e((string) $s['estudiantes']) ?></strong>
                <small>Matrículas activas</small>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-gold">
            <div class="stat-icon"><i class="bi bi-calendar-check-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Asistencia promedio</span>
                <strong><?= e(porcentaje((float) $s['asistencia'], 1)) ?></strong>
                <small>Meta institucional: 80 %</small>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-red">
            <div class="stat-icon"><i class="bi bi-clipboard2-check-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Evaluaciones abiertas</span>
                <strong><?= e((string) $s['evaluaciones']) ?></strong>
                <small>Pendientes de cerrar o calificar</small>
            </div>
        </article>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-7">
        <section class="panel-card h-100">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Docencia</span>
                    <h3>Mis cursos</h3>
                </div>
                <span class="soft-badge"><i class="bi bi-clock-history" aria-hidden="true"></i> Periodo actual</span>
            </div>

            <?php if ($d['cursos'] === []): ?>
                <div class="empty-state compact">No tienes cursos asignados.</div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($d['cursos'] as $curso): ?>
                        <?php
                        $cupo = max(1, (int) $curso['cupo_maximo']);
                        $ocupacion = min(100, (int) round(((int) $curso['matriculados'] / $cupo) * 100));
                        ?>
                        <article class="course-tile">
                            <div class="course-tile-head">
                                <span class="level-pill"><?= e((string) $curso['nivel_codigo']) ?></span>
                                <span class="course-code"><?= e((string) $curso['codigo']) ?></span>
                            </div>
                            <h4><?= e((string) $curso['curso']) ?></h4>
                            <p><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e((string) $curso['aula']) ?> · <span class="text-capitalize"><?= e(strtolower((string) $curso['jornada'])) ?></span></p>
                            <div class="course-progress">
                                <div class="d-flex justify-content-between">
                                    <span><?= e((string) $curso['matriculados']) ?> estudiantes</span>
                                    <span><?= e((string) $ocupacion) ?> %</span>
                                </div>
                                <div class="progress mini-progress" role="progressbar"
                                     aria-label="Ocupación de <?= e((string) $curso['curso']) ?>"
                                     aria-valuenow="<?= e((string) $ocupacion) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: <?= e((string) $ocupacion) ?>%"></div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="panel-card h-100">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Agenda</span>
                    <h3>Próximas sesiones</h3>
                </div>
                <span class="counter-badge"><?= e((string) count($d['sesiones'])) ?></span>
            </div>

            <?php if ($d['sesiones'] === []): ?>
                <div class="empty-inline"><i class="bi bi-calendar2-check" aria-hidden="true"></i> No hay sesiones programadas.</div>
            <?php else: ?>
                <ul class="agenda-list">
                    <?php foreach ($d['sesiones'] as $sesion): ?>
                        <li>
                            <span class="agenda-date">
                                <strong><?= e(fecha_objeto((string) $sesion['fecha'])?->format('d') ?? '—') ?></strong>
                                <small><?= e(mb_strtoupper(mb_substr(mes_nombre((int) fecha_objeto((string) $sesion['fecha'])?->format('n')), 0, 3, 'UTF-8'), 'UTF-8')) ?></small>
                            </span>
                            <span class="agenda-body">
                                <strong><?= e((string) $sesion['tema']) ?></strong>
                                <small><?= e((string) $sesion['codigo']) ?> · <?= e(hora_corta((string) $sesion['hora_inicio'])) ?>–<?= e(hora_corta((string) $sesion['hora_fin'])) ?> · <?= e((string) $sesion['aula']) ?></small>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="panel-divider"></div>

            <div class="panel-head panel-head-tight">
                <div>
                    <span class="eyebrow">Evaluaciones</span>
                    <h3>Próximas entregas</h3>
                </div>
            </div>

            <?php if ($d['evaluaciones'] === []): ?>
                <div class="empty-inline"><i class="bi bi-check2-circle" aria-hidden="true"></i> Sin evaluaciones pendientes.</div>
            <?php else: ?>
                <ul class="compact-list">
                    <?php foreach ($d['evaluaciones'] as $evaluacion): ?>
                        <?php $urgencia = urgencia_evento((string) $evaluacion['fecha_entrega']); ?>
                        <li>
                            <span class="urgency-dot urgency-<?= e($urgencia['clase']) ?>" aria-hidden="true"></span>
                            <span>
                                <strong><?= e((string) $evaluacion['nombre']) ?></strong>
                                <small><?= e((string) $evaluacion['codigo']) ?> · <?= e(fecha_hora((string) $evaluacion['fecha_entrega'])) ?></small>
                            </span>
                            <span class="badge-soft badge-<?= e($urgencia['clase']) ?>"><?= e($urgencia['etiqueta']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<section class="panel-card mt-4">
    <div class="panel-head">
        <div>
            <span class="eyebrow">Acompañamiento</span>
            <h3>Estudiantes que requieren apoyo</h3>
            <p class="panel-subtitle">Promedio inferior a 3.0 o asistencia menor al 80 %.</p>
        </div>
        <span class="soft-badge"><i class="bi bi-life-preserver" aria-hidden="true"></i> Seguimiento</span>
    </div>

    <?php if ($d['riesgo'] === []): ?>
        <div class="empty-state compact">Ningún estudiante está por debajo de los umbrales.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Estudiante</th>
                        <th scope="col">Curso</th>
                        <th scope="col">Avance</th>
                        <th scope="col">Promedio</th>
                        <th scope="col">Asistencia</th>
                        <th scope="col" class="text-end">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($d['riesgo'] as $estudiante): ?>
                        <tr>
                            <td>
                                <div class="person-cell">
                                    <span class="avatar avatar-xs" aria-hidden="true"><?= e(iniciales((string) $estudiante['estudiante'])) ?></span>
                                    <strong><?= e((string) $estudiante['estudiante']) ?></strong>
                                </div>
                            </td>
                            <td><span class="table-sub"><?= e((string) $estudiante['curso']) ?></span></td>
                            <td><?= e(porcentaje((float) $estudiante['porcentaje_avance'])) ?></td>
                            <td><span class="badge-soft badge-<?= e(nota_clase((float) $estudiante['promedio_ponderado'])) ?>"><?= e(number_format((float) $estudiante['promedio_ponderado'], 2, ',', '.')) ?></span></td>
                            <td><span class="badge-soft badge-<?= (float) $estudiante['porcentaje_asistencia'] < 80 ? 'danger' : 'success' ?>"><?= e(porcentaje((float) $estudiante['porcentaje_asistencia'], 1)) ?></span></td>
                            <td class="text-end"><span class="status-pill status-risk">Requiere apoyo</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
