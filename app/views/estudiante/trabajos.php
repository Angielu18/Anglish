<?php
/**
 * Vista: Entregar trabajos y ver notas (Módulo de Estudiantes).
 * Variables: $trabajos, $user.
 */
$listaTrabajos = is_array($trabajos ?? null) ? $trabajos : [];
?>
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <span class="badge bg-primary-subtle text-primary mb-1">Módulo de Estudiantes</span>
                    <h2 class="h4 fw-bold mb-0">Entregar trabajos y consultar calificaciones</h2>
                    <p class="text-muted small mb-0">Sube tus entregas a tiempo, verifica tus notas obtenidas y revisa las observaciones de tu docente.</p>
                </div>
                <a href="<?= e(url('estudiante/reportes')) ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-graph-up-arrow me-1"></i> Ver mi reporte general
                </a>
            </div>

            <?php if ($listaTrabajos === []): ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                    <p class="mt-2 mb-0 text-muted">No tienes tareas o evaluaciones registradas en este momento.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Actividad / Evaluación</th>
                                <th>Curso</th>
                                <th>Fecha límite</th>
                                <th>Peso</th>
                                <th>Estado de entrega</th>
                                <th>Nota (1.0–5.0)</th>
                                <th>Retroalimentación docente</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listaTrabajos as $item): ?>
                                <?php
                                $evalId = (int) $item['evaluacion_id'];
                                $matId  = (int) $item['matricula_id'];
                                $estadoEntrega = (string) ($item['estado_entrega'] ?? 'PENDIENTE');
                                $nota = $item['nota'] !== null ? (float) $item['nota'] : null;
                                $retro = (string) ($item['retroalimentacion'] ?? ($item['nota_observacion'] ?? ''));
                                ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?= e((string) $item['evaluacion_nombre']) ?></strong>
                                        <span class="badge bg-secondary-subtle text-secondary small"><?= e((string) $item['tipo']) ?></span>
                                        <?php if (!empty($item['descripcion'])): ?>
                                            <small class="d-block text-muted mt-1"><?= e((string) $item['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= e((string) $item['nivel_codigo']) ?></span>
                                        <small class="d-block text-muted"><?= e((string) $item['curso_nombre']) ?></small>
                                    </td>
                                    <td class="small">
                                        <i class="bi bi-calendar-event me-1 text-muted"></i>
                                        <?= e((string) $item['fecha_entrega']) ?>
                                    </td>
                                    <td><strong><?= e(porcentaje((float) $item['porcentaje'])) ?></strong></td>
                                    <td>
                                        <?php if ($estadoEntrega === 'CALIFICADA' || $nota !== null): ?>
                                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle-fill me-1"></i>Calificada</span>
                                        <?php elseif ($estadoEntrega === 'ENTREGADA'): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis"><i class="bi bi-cloud-check-fill me-1"></i>Entregada</span>
                                            <small class="d-block text-muted" style="font-size: 0.72rem;"><?= e((string) ($item['archivo_nombre'] ?? '')) ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-hourglass-split me-1"></i>Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($nota !== null): ?>
                                            <span class="fs-6 fw-bold <?= $nota >= 3.0 ? 'text-success' : 'text-danger' ?>">
                                                <?= e(number_format($nota, 1, '.', '')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin calificar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small" style="max-width: 240px;">
                                        <?php if ($retro !== ''): ?>
                                            <span class="text-dark"><i class="bi bi-chat-left-quote text-primary me-1"></i><?= e($retro) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Pendiente de revisión</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEntregar<?= $evalId ?>">
                                            <i class="bi bi-upload me-1"></i> <?= $estadoEntrega === 'PENDIENTE' ? 'Entregar' : 'Actualizar' ?>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de entrega -->
                                <div class="modal fade" id="modalEntregar<?= $evalId ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="<?= e(url('estudiante/entregar')) ?>" method="post" class="modal-content rounded-4 border-0 shadow">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="evaluacion_id" value="<?= $evalId ?>">
                                            <input type="hidden" name="matricula_id" value="<?= $matId ?>">

                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Entregar trabajo: <?= e((string) $item['evaluacion_nombre']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small mb-3">
                                                    Curso: <strong><?= e((string) $item['curso_nombre']) ?></strong> · Peso: <strong><?= e(porcentaje((float) $item['porcentaje'])) ?></strong>
                                                </p>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nombre del archivo o documento *</label>
                                                    <input type="text" name="archivo_nombre" class="form-control"
                                                           value="<?= e((string) ($item['archivo_nombre'] ?? '')) ?>"
                                                           placeholder="Ej. taller_unidad1_sara.pdf" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Enlace del trabajo (Drive / OneDrive / Repositorio)</label>
                                                    <input type="text" name="archivo_url" class="form-control"
                                                           value="<?= e((string) ($item['archivo_url'] ?? '')) ?>"
                                                           placeholder="https://drive.google.com/...">
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Comentarios para el profesor *</label>
                                                    <textarea name="comentario_estudiante" class="form-control" rows="3"
                                                              placeholder="Describe brevemente tu entrega o dudas sobre la actividad..." required><?= e((string) ($item['comentario_estudiante'] ?? '')) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-cloud-arrow-up me-1"></i> Enviar entrega
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
