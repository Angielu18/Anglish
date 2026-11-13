<?php
/**
 * Tablero del Estudiante.
 * Variables: $dashboard, $user, $currentDate.
 */
$d = $dashboard;
$m = $d['matricula'];
$r = $d['resumen'];
$nombre = (string) ($user['nombres'] ?? 'Estudiante');
$promedioOk = (float) $r['promedio'] >= (float) config('app.nota_minima', 3.0);
$asistenciaOk = (float) $r['asistencia'] >= (float) config('app.asistencia_minima', 80.0);
$progreso = min(100, max(0, (float) $r['progreso']));
$asistenciaPct = min(100, max(0, (float) $r['asistencia']));
$alertasSinLeer = (int) $r['alertas_sin_leer'];

$iconosAlerta = [
    'EVALUACION' => 'bi-clipboard2-check',
    'PAGO' => 'bi-credit-card',
    'ASISTENCIA' => 'bi-calendar-check',
    'RENDIMIENTO' => 'bi-graph-down-arrow',
];
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">Mi proceso</span>
        <h2>Hola, <?= e($nombre) ?> <span class="wave" aria-hidden="true">👋</span></h2>
        <p>Este es el resumen de tu curso y tus próximos compromisos.</p>
    </div>
    <div class="page-date">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        <span><?= e($currentDate) ?></span>
    </div>
</div>

<?php if ($m === null): ?>
    <section class="panel-card empty-enrollment">
        <span class="empty-icon"><i class="bi bi-journal-x" aria-hidden="true"></i></span>
        <h3>Aún no tienes una matrícula activa</h3>
        <p>Cuando el administrador registre tu matrícula, aquí aparecerá tu curso, progreso y alertas.</p>
    </section>
<?php else: ?>
    <section class="student-hero">
        <div class="student-hero-main">
            <div class="hero-topline">
                <span class="level-pill level-pill-light"><?= e((string) $m['nivel_codigo']) ?></span>
                <span class="hero-code"><?= e((string) $m['codigo']) ?></span>
            </div>
            <h3><?= e((string) $m['curso']) ?></h3>
            <p class="hero-professor">
                <i class="bi bi-person-video3" aria-hidden="true"></i>
                <?= e((string) $m['profesor']) ?>
                <span class="hero-dot" aria-hidden="true">·</span>
                <?= e((string) $m['profesor_email']) ?>
            </p>
            <div class="hero-meta">
                <span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e((string) $m['aula']) ?></span>
                <span><i class="bi bi-briefcase" aria-hidden="true"></i> <span class="text-capitalize"><?= e(strtolower((string) $m['jornada'])) ?></span></span>
                <span><i class="bi bi-calendar-range" aria-hidden="true"></i> <?= e(fecha_corta((string) $m['fecha_inicio'])) ?> — <?= e(fecha_corta((string) $m['fecha_fin'])) ?></span>
            </div>
        </div>
        <div class="student-hero-side">
            <span class="hero-ring" style="--ring-value: <?= e((string) $progreso) ?>">
                <strong><?= e((string) (int) round($progreso)) ?>%</strong>
                <small>avance</small>
            </span>
            <div class="hero-side-copy">
                <span>Nivel actual</span>
                <strong><?= e((string) $m['nivel_codigo']) ?> · <?= e((string) $m['nivel_nombre']) ?></strong>
                <small><?= e((string) $m['numero_cuotas']) ?> cuotas · <?= e(money((float) $m['valor_total'])) ?></small>
            </div>
        </div>
    </section>

    <div class="row g-3 stat-grid">
        <div class="col-sm-6 col-xl-3">
            <article class="stat-card tone-teal">
                <div class="stat-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></div>
                <div class="stat-body">
                    <span class="stat-label">Progreso académico</span>
                    <strong><?= e(porcentaje($progreso)) ?></strong>
                    <small><?= e((string) ($d['progreso']['evaluaciones_calificadas'] ?? 0)) ?> de <?= e((string) ($d['progreso']['evaluaciones_total'] ?? 0)) ?> evaluaciones calificadas</small>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="stat-card <?= $promedioOk ? 'tone-teal' : 'tone-red' ?>">
                <div class="stat-icon"><i class="bi bi-award" aria-hidden="true"></i></div>
                <div class="stat-body">
                    <span class="stat-label">Promedio ponderado</span>
                    <strong><?= e(number_format((float) $r['promedio'], 2, ',', '.')) ?> <small class="stat-of">/ 5.0</small></strong>
                    <small class="<?= $promedioOk ? 'text-success' : 'text-danger' ?>"><?= $promedioOk ? 'Cumples la nota mínima' : 'Meta mínima: ' . e(number_format((float) config('app.nota_minima', 3.0), 1, ',', '.')) ?></small>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="stat-card <?= $asistenciaOk ? 'tone-blue' : 'tone-gold' ?>">
                <div class="stat-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></div>
                <div class="stat-body">
                    <span class="stat-label">Asistencia</span>
                    <strong><?= e(porcentaje((float) $r['asistencia'], 1)) ?></strong>
                    <small><?= e((string) ($d['asistencia']['presentes'] ?? 0)) ?> presentes · <?= e((string) ($d['asistencia']['ausentes'] ?? 0)) ?> ausencias</small>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="stat-card <?= (float) $r['saldo'] > 0 ? 'tone-red' : 'tone-teal' ?>">
                <div class="stat-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></div>
                <div class="stat-body">
                    <span class="stat-label">Saldo pendiente</span>
                    <strong class="stat-money"><?= e(money((float) $r['saldo'])) ?></strong>
                    <span class="badge-soft badge-<?= e(cartera_clase((string) $r['cartera'])) ?>"><?= e((string) $r['cartera']) ?></span>
                </div>
            </article>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Rendimiento</span>
                        <h3>Tu avance del curso</h3>
                    </div>
                    <span class="soft-badge"><i class="bi bi-activity" aria-hidden="true"></i> En vivo</span>
                </div>

                <div class="metric-block">
                    <div class="metric-head">
                        <span>Contenido evaluado</span>
                        <strong><?= e(porcentaje($progreso)) ?></strong>
                    </div>
                    <div class="progress app-progress" role="progressbar" aria-label="Progreso del curso" aria-valuenow="<?= e((string) $progreso) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: <?= e((string) $progreso) ?>%"></div>
                    </div>
                    <p class="metric-note">Se calcula con el peso de las evaluaciones ya calificadas.</p>
                </div>

                <div class="metric-block">
                    <div class="metric-head">
                        <span>Asistencia efectiva</span>
                        <strong><?= e(porcentaje((float) $r['asistencia'], 1)) ?></strong>
                    </div>
                    <div class="progress app-progress app-progress-blue" role="progressbar" aria-label="Asistencia" aria-valuenow="<?= e((string) $asistenciaPct) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: <?= e((string) $asistenciaPct) ?>%"></div>
                    </div>
                    <p class="metric-note">Presentes, tardanzas y excusas cuentan como asistencia efectiva.</p>
                </div>

                <div class="insight-row">
                    <div class="insight <?= $promedioOk ? 'insight-ok' : 'insight-risk' ?>">
                        <i class="bi <?= $promedioOk ? 'bi-check2-circle' : 'bi-exclamation-triangle' ?>" aria-hidden="true"></i>
                        <div>
                            <strong><?= $promedioOk ? 'Promedio aprobatorio' : 'Promedio por debajo de la meta' ?></strong>
                            <span>Nota mínima institucional: 3.0 / 5.0.</span>
                        </div>
                    </div>
                    <div class="insight <?= $asistenciaOk ? 'insight-ok' : 'insight-risk' ?>">
                        <i class="bi <?= $asistenciaOk ? 'bi-check2-circle' : 'bi-exclamation-triangle' ?>" aria-hidden="true"></i>
                        <div>
                            <strong><?= $asistenciaOk ? 'Asistencia en regla' : 'Asistencia en riesgo' ?></strong>
                            <span>Meta institucional: 80 % de asistencia efectiva.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel-card mt-4">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Entregas</span>
                        <h3>Próximas evaluaciones</h3>
                    </div>
                    <span class="soft-badge"><i class="bi bi-hourglass-split" aria-hidden="true"></i> <?= e((string) count($d['evaluaciones'])) ?> pendientes</span>
                </div>

                <?php if ($d['evaluaciones'] === []): ?>
                    <div class="empty-inline"><i class="bi bi-check2-circle" aria-hidden="true"></i> No tienes entregas pendientes. ¡Buen trabajo!</div>
                <?php else: ?>
                    <ul class="evaluation-list">
                        <?php foreach ($d['evaluaciones'] as $evaluacion): ?>
                            <?php $urgencia = $evaluacion['urgencia']; ?>
                            <li class="evaluation-item urgency-border-<?= e($urgencia['clase']) ?>">
                                <span class="evaluation-icon"><i class="bi <?= e($urgencia['icono']) ?>" aria-hidden="true"></i></span>
                                <div class="evaluation-main">
                                    <div class="evaluation-title">
                                        <strong><?= e((string) $evaluacion['nombre']) ?></strong>
                                        <span class="badge-soft badge-<?= e($urgencia['clase']) ?>"><?= e($urgencia['etiqueta']) ?></span>
                                    </div>
                                    <p><?= e(tipo_evaluacion((string) $evaluacion['tipo'])) ?> · <?= e(number_format((float) $evaluacion['porcentaje'], 0)) ?> % de la nota</p>
                                    <small><i class="bi bi-clock" aria-hidden="true"></i> <?= e(fecha_hora((string) $evaluacion['fecha_entrega'])) ?> · <?= e($urgencia['detalle']) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="panel-card mt-4">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Registro</span>
                        <h3>Asistencia reciente</h3>
                    </div>
                    <span class="soft-badge"><i class="bi bi-journal-check" aria-hidden="true"></i> Últimas 5 sesiones</span>
                </div>

                <?php if ($d['asistencias'] === []): ?>
                    <div class="empty-inline"><i class="bi bi-calendar2" aria-hidden="true"></i> Aún no hay asistencias registradas.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table app-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Tema de la sesión</th>
                                    <th scope="col" class="text-end">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($d['asistencias'] as $asistencia): ?>
                                    <?php $estado = estado_asistencia((string) $asistencia['estado']); ?>
                                    <tr>
                                        <td><span class="table-sub"><?= e(fecha_corta((string) $asistencia['fecha'])) ?></span></td>
                                        <td><strong class="table-strong"><?= e((string) $asistencia['tema']) ?></strong></td>
                                        <td class="text-end">
                                            <span class="badge-soft badge-<?= e($estado['clase']) ?>">
                                                <i class="bi <?= e($estado['icono']) ?>" aria-hidden="true"></i>
                                                <?= e($estado['etiqueta']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="panel-card alert-panel" id="alertas">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Seguimiento</span>
                        <h3>Centro de alertas</h3>
                    </div>
                    <span class="counter-badge <?= $alertasSinLeer > 0 ? 'counter-alert' : '' ?>"><?= e((string) $alertasSinLeer) ?></span>
                </div>

                <?php if ($alertasSinLeer > 0): ?>
                    <form action="<?= e(url('alerts/read')) ?>" method="post" class="alert-action">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-soft">
                            <i class="bi bi-check2-all" aria-hidden="true"></i> Marcar todas como leídas
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($d['alertas'] === []): ?>
                    <div class="empty-inline"><i class="bi bi-bell-slash" aria-hidden="true"></i> No tienes alertas por ahora.</div>
                <?php else: ?>
                    <ul class="alert-list">
                        <?php foreach ($d['alertas'] as $alerta): ?>
                            <?php
                            $urgencia = $alerta['urgencia'];
                            $icono = $iconosAlerta[$alerta['tipo']] ?? 'bi-bell';
                            ?>
                            <li class="alert-item <?= (int) $alerta['leida'] === 1 ? 'is-read' : '' ?>">
                                <span class="alert-icon urgency-bg-<?= e($urgencia['clase']) ?>">
                                    <i class="bi <?= e($icono) ?>" aria-hidden="true"></i>
                                </span>
                                <div class="alert-copy">
                                    <div class="alert-title">
                                        <strong><?= e((string) $alerta['titulo']) ?></strong>
                                        <?php if ((int) $alerta['leida'] === 0): ?>
                                            <span class="unread-dot" aria-label="Sin leer"></span>
                                        <?php endif; ?>
                                    </div>
                                    <p><?= e((string) $alerta['mensaje']) ?></p>
                                    <small>
                                        <span class="badge-soft badge-<?= e($urgencia['clase']) ?>"><?= e($urgencia['etiqueta']) ?></span>
                                        <?= e($urgencia['detalle']) ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="panel-card mt-4">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Finanzas</span>
                        <h3>Estado de cuenta</h3>
                    </div>
                    <span class="badge-soft badge-<?= e(cartera_clase((string) $r['cartera'])) ?>"><?= e((string) $r['cartera']) ?></span>
                </div>

                <div class="balance-block">
                    <span>Saldo pendiente</span>
                    <strong><?= e(money((float) $r['saldo'])) ?></strong>
                    <small>Total del curso: <?= e(money((float) $m['valor_total'])) ?></small>
                </div>

                <?php if ($d['cuotas'] === []): ?>
                    <div class="empty-inline"><i class="bi bi-check2-circle" aria-hidden="true"></i> No tienes cuotas pendientes.</div>
                <?php else: ?>
                    <ul class="installment-list">
                        <?php foreach ($d['cuotas'] as $cuota): ?>
                            <li>
                                <div>
                                    <strong>Cuota <?= e((string) $cuota['numero']) ?> · <?= e((string) $cuota['periodo']) ?></strong>
                                    <small>Vence <?= e(fecha_corta((string) $cuota['fecha_vencimiento'])) ?></small>
                                </div>
                                <div class="installment-right">
                                    <strong><?= e(money((float) $cuota['saldo'])) ?></strong>
                                    <span class="badge-soft badge-<?= e($cuota['urgencia']['clase']) ?>"><?= e($cuota['urgencia']['etiqueta']) ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="panel-card mt-4">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Horario</span>
                        <h3>Tu semana</h3>
                    </div>
                    <span class="soft-badge"><i class="bi bi-clock" aria-hidden="true"></i> <?= e((string) count($d['horarios'])) ?> bloques</span>
                </div>

                <?php if ($d['horarios'] === []): ?>
                    <div class="empty-inline"><i class="bi bi-calendar2" aria-hidden="true"></i> El horario se publicará pronto.</div>
                <?php else: ?>
                    <ul class="schedule-list">
                        <?php foreach ($d['horarios'] as $horario): ?>
                            <li>
                                <span class="schedule-day"><?= e(dia_nombre((int) $horario['dia_semana'])) ?></span>
                                <span class="schedule-time"><?= e(hora_corta((string) $horario['hora_inicio'])) ?> – <?= e(hora_corta((string) $horario['hora_fin'])) ?></span>
                                <span class="schedule-room"><?= e((string) $horario['aula']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
    </div>
<?php endif; ?>
