<?php
/**
 * Tablero del Administrador.
 * Variables: $dashboard, $user, $currentDate.
 */
$d = $dashboard;
$c = $d['conteos'];

$tipoAlerta = [
    'EVALUACION' => ['Evaluaciones', 'bi-clipboard2-check', 'tone-teal'],
    'PAGO'       => ['Pagos', 'bi-credit-card', 'tone-gold'],
    'ASISTENCIA' => ['Asistencia', 'bi-calendar-check', 'tone-blue'],
    'RENDIMIENTO' => ['Rendimiento', 'bi-graph-down-arrow', 'tone-red'],
];
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">Vista general</span>
        <h2>Todo el pulso de la academia</h2>
        <p>Indicadores académicos, financieros y de actividad reciente.</p>
    </div>
    <div class="page-date">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        <span><?= e($currentDate) ?></span>
    </div>
</div>

<div class="row g-3 stat-grid">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-teal">
            <div class="stat-icon"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Estudiantes activos</span>
                <strong><?= e((string) $c['estudiantes']) ?></strong>
                <small><?= e((string) $c['matriculas']) ?> matrículas vigentes</small>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-blue">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Cursos en curso</span>
                <strong><?= e((string) $c['cursos']) ?></strong>
                <small><?= e((string) $c['profesores']) ?> profesores activos</small>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-gold">
            <div class="stat-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Recaudo del mes</span>
                <strong class="stat-money"><?= e(money((float) $d['recaudo_mes'])) ?></strong>
                <small>Periodo <?= e((string) $d['periodo']) ?></small>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card tone-red">
            <div class="stat-icon"><i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i></div>
            <div class="stat-body">
                <span class="stat-label">Cartera vencida</span>
                <strong class="stat-money"><?= e(money((float) $d['cartera_total'])) ?></strong>
                <small><?= e((string) $d['cartera_cuotas']) ?> cuotas por cobrar</small>
            </div>
        </article>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <section class="panel-card h-100">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Oferta académica</span>
                    <h3>Ocupación de cursos</h3>
                </div>
                <span class="soft-badge"><i class="bi bi-people" aria-hidden="true"></i> <?= e((string) $c['matriculas']) ?> estudiantes</span>
            </div>

            <?php if ($d['cursos'] === []): ?>
                <div class="empty-state compact">Aún no hay cursos registrados.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Curso</th>
                                <th scope="col">Nivel</th>
                                <th scope="col">Profesor</th>
                                <th scope="col">Jornada</th>
                                <th scope="col" class="text-end">Ocupación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($d['cursos'] as $curso): ?>
                                <?php
                                $cupo = max(1, (int) $curso['cupo_maximo']);
                                $ocupacion = min(100, (int) round(((int) $curso['matriculados'] / $cupo) * 100));
                                ?>
                                <tr>
                                    <td>
                                        <strong class="table-strong"><?= e((string) $curso['curso']) ?></strong>
                                        <small class="table-sub"><?= e((string) $curso['codigo']) ?> · Aula <?= e((string) $curso['aula']) ?></small>
                                    </td>
                                    <td><span class="level-pill"><?= e((string) $curso['nivel_codigo']) ?></span></td>
                                    <td><?= e((string) $curso['profesor']) ?></td>
                                    <td><span class="text-capitalize"><?= e(strtolower((string) $curso['jornada'])) ?></span></td>
                                    <td class="text-end occupancy-cell">
                                        <div class="occupancy-line">
                                            <span><?= e((string) $curso['matriculados']) ?>/<?= e((string) $curso['cupo_maximo']) ?></span>
                                            <div class="progress mini-progress" role="progressbar"
                                                 aria-label="Ocupación de <?= e((string) $curso['curso']) ?>"
                                                 aria-valuenow="<?= e((string) $ocupacion) ?>" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" style="width: <?= e((string) $ocupacion) ?>%"></div>
                                            </div>
                                        </div>
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
        <section class="panel-card finance-card">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Finanzas</span>
                    <h3>Resumen del periodo</h3>
                </div>
                <span class="finance-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
            </div>

            <div class="finance-row">
                <span>Recaudo del mes</span>
                <strong><?= e(money((float) $d['recaudo_mes'])) ?></strong>
            </div>
            <div class="finance-row">
                <span>Saldo en mora</span>
                <strong class="text-danger"><?= e(money((float) $d['cartera_total'])) ?></strong>
            </div>
            <div class="finance-row">
                <span>Asistencia promedio</span>
                <strong><?= e(porcentaje((float) $d['asistencia_promedio'], 1)) ?></strong>
            </div>

            <div class="finance-callout">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <p>Los valores provienen de las vistas SQL de cartera e ingresos; no se recalculan en la vista.</p>
            </div>
        </section>

        <section class="panel-card mt-4">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Seguimiento</span>
                    <h3>Alertas sin leer</h3>
                </div>
                <span class="counter-badge"><?= e((string) $c['alertas']) ?></span>
            </div>

            <?php if ($d['alertas_tipo'] === []): ?>
                <div class="empty-inline"><i class="bi bi-check2-circle" aria-hidden="true"></i> Sin alertas pendientes.</div>
            <?php else: ?>
                <ul class="type-list">
                    <?php foreach ($d['alertas_tipo'] as $tipo): ?>
                        <?php $meta = $tipoAlerta[$tipo['tipo']] ?? [$tipo['tipo'], 'bi-bell', 'tone-blue']; ?>
                        <li>
                            <span class="type-icon <?= e($meta[2]) ?>"><i class="bi <?= e($meta[1]) ?>" aria-hidden="true"></i></span>
                            <span><?= e($meta[0]) ?></span>
                            <strong><?= e((string) $tipo['total']) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-7">
        <section class="panel-card h-100">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Movimientos</span>
                    <h3>Pagos recientes</h3>
                </div>
                <span class="soft-badge"><i class="bi bi-arrow-down-left" aria-hidden="true"></i> Últimos 6</span>
            </div>

            <?php if ($d['pagos'] === []): ?>
                <div class="empty-state compact">Aún no hay pagos registrados.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Estudiante</th>
                                <th scope="col">Curso</th>
                                <th scope="col">Método</th>
                                <th scope="col">Fecha</th>
                                <th scope="col" class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($d['pagos'] as $pago): ?>
                                <tr>
                                    <td><strong class="table-strong"><?= e((string) $pago['estudiante']) ?></strong></td>
                                    <td><span class="table-sub"><?= e((string) $pago['curso']) ?></span></td>
                                    <td><span class="status-dot"></span><?= e(metodo_pago((string) $pago['metodo_pago'])) ?></td>
                                    <td><span class="table-sub"><?= e(fecha_hora((string) $pago['fecha_pago'])) ?></span></td>
                                    <td class="text-end"><strong><?= e(money((float) $pago['valor'])) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="panel-card h-100">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Trazabilidad</span>
                    <h3>Actividad reciente</h3>
                </div>
                <span class="soft-badge"><i class="bi bi-shield-check" aria-hidden="true"></i> Auditoría</span>
            </div>

            <?php if ($d['auditoria'] === []): ?>
                <div class="empty-state compact">Sin actividad registrada.</div>
            <?php else: ?>
                <ol class="activity-timeline">
                    <?php foreach ($d['auditoria'] as $log): ?>
                        <li>
                            <span class="activity-dot" aria-hidden="true"></span>
                            <div>
                                <strong><?= e((string) $log['usuario']) ?></strong>
                                <span><?= e((string) $log['accion']) ?> · <?= e((string) $log['modulo']) ?></span>
                                <small><?= e(fecha_hora((string) $log['created_at'])) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    </div>
</div>
