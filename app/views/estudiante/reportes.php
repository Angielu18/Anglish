<?php
/**
 * Vista: Reportes de progreso y asistencia (Módulo de Estudiantes).
 * Variables: $reporte, $user.
 */
$progreso = $reporte['progreso'] ?? null;
$asistencia = $reporte['asistencia'] ?? null;
$calificaciones = is_array($reporte['calificaciones'] ?? null) ? $reporte['calificaciones'] : [];
$asistenciasDetalle = is_array($reporte['asistencias_detalle'] ?? null) ? $reporte['asistencias_detalle'] : [];

$porcentajeAvance = isset($progreso['porcentaje_avance']) ? (float) $progreso['porcentaje_avance'] : 0.0;
$promedioPonderado = isset($progreso['promedio_ponderado']) ? (float) $progreso['promedio_ponderado'] : 0.0;
$porcentajeAsistencia = isset($asistencia['porcentaje_asistencia']) ? (float) $asistencia['porcentaje_asistencia'] : 0.0;
?>
<div class="row g-4">
    <!-- Tarjetas de resumen del reporte -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <span class="text-muted small fw-semibold text-uppercase">Promedio Acumulado</span>
            <div class="d-flex align-items-baseline justify-content-between mt-2">
                <h3 class="display-6 fw-bold mb-0 <?= $promedioPonderado >= 3.0 ? 'text-success' : 'text-danger' ?>">
                    <?= e(number_format($promedioPonderado, 2, '.', '')) ?>
                </h3>
                <span class="badge <?= $promedioPonderado >= 3.0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                    <?= $promedioPonderado >= 3.0 ? 'Aprobando' : 'En riesgo (< 3.0)' ?>
                </span>
            </div>
            <p class="text-muted small mt-2 mb-0">Meta mínima institucional: 3.0 sobre 5.0</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <span class="text-muted small fw-semibold text-uppercase">Porcentaje de Asistencia</span>
            <div class="d-flex align-items-baseline justify-content-between mt-2">
                <h3 class="display-6 fw-bold mb-0 <?= $porcentajeAsistencia >= 80.0 ? 'text-primary' : 'text-warning' ?>">
                    <?= e(porcentaje($porcentajeAsistencia)) ?>
                </h3>
                <span class="badge <?= $porcentajeAsistencia >= 80.0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis' ?>">
                    <?= $porcentajeAsistencia >= 80.0 ? 'Cumple meta' : 'Alerta (< 80%)' ?>
                </span>
            </div>
            <div class="progress mt-3" style="height: 8px;">
                <div class="progress-bar <?= $porcentajeAsistencia >= 80.0 ? 'bg-primary' : 'bg-warning' ?>"
                     style="width: <?= min(100, (int) $porcentajeAsistencia) ?>%"></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <span class="text-muted small fw-semibold text-uppercase">Avance Evaluativo del Curso</span>
            <div class="d-flex align-items-baseline justify-content-between mt-2">
                <h3 class="display-6 fw-bold mb-0 text-dark"><?= e(porcentaje($porcentajeAvance)) ?></h3>
                <span class="badge bg-info-subtle text-info-emphasis"><?= e((string) ($progreso['curso_nombre'] ?? 'Curso activo')) ?></span>
            </div>
            <div class="progress mt-3" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: <?= min(100, (int) $porcentajeAvance) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Detalle de Calificaciones -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h3 class="h5 fw-bold mb-3"><i class="bi bi-award text-primary me-2"></i>Historial de Calificaciones</h3>
            <?php if ($calificaciones === []): ?>
                <p class="text-muted small mb-0">Aún no se registran calificaciones en tu historial.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Evaluación</th>
                                <th>Peso</th>
                                <th>Nota</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($calificaciones as $cal): ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?= e((string) $cal['evaluacion']) ?></strong>
                                        <small class="text-muted"><?= e((string) $cal['tipo']) ?></small>
                                    </td>
                                    <td><?= e(porcentaje((float) $cal['porcentaje'])) ?></td>
                                    <td>
                                        <span class="badge <?= (float) $cal['nota'] >= 3.0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= e(number_format((float) $cal['nota'], 1, '.', '')) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= e((string) ($cal['observacion'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalle de Asistencias -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h3 class="h5 fw-bold mb-3"><i class="bi bi-calendar-check text-success me-2"></i>Registro Detallado de Asistencia</h3>
            <?php if ($asistenciasDetalle === []): ?>
                <p class="text-muted small mb-0">No hay sesiones registradas todavía.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Tema de clase</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asistenciasDetalle as $asis): ?>
                                <?php $est = (string) $asis['estado']; ?>
                                <tr>
                                    <td class="small"><?= e((string) $asis['fecha']) ?></td>
                                    <td>
                                        <span class="d-block small fw-semibold"><?= e((string) $asis['tema']) ?></span>
                                        <?php if (!empty($asis['observacion'])): ?>
                                            <small class="text-muted"><?= e((string) $asis['observacion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($est === 'PRESENTE'): ?>
                                            <span class="badge bg-success-subtle text-success">Presente</span>
                                        <?php elseif ($est === 'TARDE'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Tarde</span>
                                        <?php elseif ($est === 'EXCUSA'): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis">Excusa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">Ausente</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
