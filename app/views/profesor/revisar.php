<?php
/**
 * Vista: Revisar trabajos de alumnos (Módulo de Profesores).
 * Variables: $entregas, $user.
 */
$listaEntregas = is_array($entregas ?? null) ? $entregas : [];
?>
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <span class="badge bg-primary-subtle text-primary mb-1">Módulo de Profesores</span>
                    <h2 class="h4 fw-bold mb-0">Revisar y calificar entregas de estudiantes</h2>
                    <p class="text-muted small mb-0">Evalúa las tareas enviadas, asigna calificaciones en escala 1.0 a 5.0 y registra retroalimentación individual.</p>
                </div>
                <a href="<?= e(url('profesor/tareas')) ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-journal-plus me-1"></i> Gestionar tareas
                </a>
            </div>

            <?php if ($listaEntregas === []): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-2 mb-0 text-muted">Aún no se han recibido entregas de tareas en tus cursos asignados.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Estudiante</th>
                                <th>Curso & Tarea</th>
                                <th>Archivo / Entrega</th>
                                <th>Comentario del alumno</th>
                                <th>Estado</th>
                                <th>Nota</th>
                                <th class="text-end">Calificar / Retroalimentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listaEntregas as $ent): ?>
                                <?php
                                $entregaId = (int) $ent['entrega_id'];
                                $notaActual = $ent['nota'] !== null ? (float) $ent['nota'] : null;
                                $estado = (string) ($ent['estado_entrega'] ?? 'ENTREGADA');
                                ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?= e((string) $ent['estudiante_nombre']) ?></strong>
                                        <small class="text-muted"><?= e((string) $ent['estudiante_codigo']) ?> · <?= e((string) $ent['estudiante_email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= e((string) $ent['curso_codigo']) ?></span>
                                        <strong class="d-block small mt-1"><?= e((string) $ent['evaluacion_nombre']) ?></strong>
                                        <small class="text-muted"><?= e((string) $ent['evaluacion_tipo']) ?> (<?= e(porcentaje((float) $ent['porcentaje'])) ?>)</small>
                                    </td>
                                    <td>
                                        <span class="d-block small fw-semibold"><i class="bi bi-file-earmark-text me-1 text-primary"></i><?= e((string) ($ent['archivo_nombre'] ?? 'Archivo adjunto')) ?></span>
                                        <small class="text-muted">Enviado: <?= e((string) $ent['fecha_envio']) ?></small>
                                    </td>
                                    <td class="small text-muted" style="max-width: 240px;">
                                        <?= e((string) ($ent['comentario_estudiante'] ?? 'Sin comentarios')) ?>
                                    </td>
                                    <td>
                                        <?php if ($estado === 'CALIFICADA' || $notaActual !== null): ?>
                                            <span class="badge bg-success-subtle text-success">Calificada</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Por calificar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($notaActual !== null): ?>
                                            <span class="fs-6 fw-bold <?= $notaActual >= 3.0 ? 'text-success' : 'text-danger' ?>">
                                                <?= e(number_format($notaActual, 1, '.', '')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalCalificar<?= $entregaId ?>">
                                            <i class="bi bi-pencil-square me-1"></i> Evaluar
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal para calificar y retroalimentar -->
                                <div class="modal fade" id="modalCalificar<?= $entregaId ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="<?= e(url('profesor/calificar')) ?>" method="post" class="modal-content rounded-4 border-0 shadow">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="entrega_id" value="<?= $entregaId ?>">

                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Evaluar entrega de <?= e((string) $ent['estudiante_nombre']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="p-3 rounded-3 bg-light mb-3 small">
                                                    <div><strong>Actividad:</strong> <?= e((string) $ent['evaluacion_nombre']) ?></div>
                                                    <div><strong>Archivo:</strong> <?= e((string) ($ent['archivo_nombre'] ?? '')) ?></div>
                                                    <div class="mt-1"><strong>Comentario del estudiante:</strong> <?= e((string) ($ent['comentario_estudiante'] ?? '')) ?></div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Calificación (Escala 1.0 a 5.0) *</label>
                                                    <input type="number" name="nota" class="form-control" step="0.1" min="1.0" max="5.0"
                                                           value="<?= e($notaActual !== null ? number_format($notaActual, 1, '.', '') : '4.0') ?>" required>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Retroalimentación y observaciones para el estudiante *</label>
                                                    <textarea name="retroalimentacion" class="form-control" rows="3"
                                                              placeholder="Escribe recomendaciones pedagógicas, puntos fuertes y aspectos a mejorar..." required><?= e((string) ($ent['retroalimentacion'] ?? ($ent['nota_observacion'] ?? ''))) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bi bi-check2-circle me-1"></i> Guardar calificación
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
