<?php
/**
 * Vista: Reportes de rendimiento (Módulo de Profesores).
 * Variables: $reporte, $user.
 */
$cursos = is_array($reporte['cursos'] ?? null) ? $reporte['cursos'] : [];
$estudiantesRiesgo = is_array($reporte['estudiantes_riesgo'] ?? null) ? $reporte['estudiantes_riesgo'] : [];
?>
<div class="row g-4">
    <!-- Rendimiento por Grupo / Curso -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <span class="badge bg-primary-subtle text-primary align-self-start mb-2">Análisis Grupal</span>
            <h3 class="h5 fw-bold mb-3">Promedio general por grupo</h3>

            <?php if ($cursos === []): ?>
                <p class="text-muted small">No tienes cursos asignados.</p>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php foreach ($cursos as $cur): ?>
                        <?php $prom = $cur['promedio_curso'] !== null ? (float) $cur['promedio_curso'] : 0.0; ?>
                        <div class="p-3 border rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="badge bg-primary me-1"><?= e((string) $cur['nivel']) ?></span>
                                    <strong><?= e((string) $cur['nombre']) ?></strong>
                                </div>
                                <span class="fs-5 fw-bold <?= $prom >= 3.0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $prom > 0 ? e(number_format($prom, 2, '.', '')) : 'S/N' ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Código: <?= e((string) $cur['codigo']) ?></span>
                                <span><i class="bi bi-people me-1"></i><?= (int) $cur['matriculados'] ?> estudiantes activos</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estudiantes en riesgo académico o inasistencia -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <span class="badge bg-danger-subtle text-danger align-self-start mb-2">Seguimiento Preventivo</span>
            <h3 class="h5 fw-bold mb-1">Estudiantes con alerta de rendimiento o asistencia</h3>
            <p class="text-muted small mb-3">Alumnos con nota ponderada menor a 3.0 o asistencia inferior al 80%.</p>

            <?php if ($estudiantesRiesgo === []): ?>
                <div class="text-center py-4">
                    <i class="bi bi-emoji-smile fs-2 text-success"></i>
                    <p class="mt-2 mb-0 text-muted small">¡Excelente! Ningún estudiante se encuentra en riesgo en tus cursos.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Estudiante</th>
                                <th>Curso</th>
                                <th>Promedio</th>
                                <th>Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantesRiesgo as $est): ?>
                                <?php
                                $nota = (float) ($est['promedio_ponderado'] ?? 0);
                                $asis = (float) ($est['porcentaje_asistencia'] ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?= e((string) $est['nombre_completo']) ?></strong>
                                        <small class="text-muted"><?= e((string) $est['codigo']) ?> · <?= e((string) $est['email']) ?></small>
                                    </td>
                                    <td class="small"><?= e((string) $est['curso']) ?></td>
                                    <td>
                                        <span class="badge <?= $nota >= 3.0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                            <?= e(number_format($nota, 2, '.', '')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $asis >= 80.0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis' ?>">
                                            <?= e(porcentaje($asis)) ?>
                                        </span>
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
