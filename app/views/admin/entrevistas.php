<?php
/**
 * Vista: Gestionar entrevistas (Módulo de Administrador).
 * Variables: $entrevistas, $niveles, $docentes, $filtroEstado, $user.
 */
$listaEntrevistas = is_array($entrevistas ?? null) ? $entrevistas : [];
$listaNiveles = is_array($niveles ?? null) ? $niveles : [];
$listaDocentes = is_array($docentes ?? null) ? $docentes : [];
$estadoActual = (string) ($filtroEstado ?? '');
?>
<div class="row g-4">
    <!-- Formulario para programar entrevista -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <span class="badge bg-primary-subtle text-primary align-self-start mb-2">Admisiones</span>
            <h3 class="h5 fw-bold mb-1">Programar entrevista</h3>
            <p class="text-muted small mb-3">Agenda una sesión de evaluación y clasificación para un nuevo aspirante.</p>

            <form action="<?= e(url('admin/entrevistas/crear')) ?>" method="post" class="d-grid gap-3">
                <?= csrf_field() ?>

                <div>
                    <label class="form-label small fw-semibold">Nombre del candidato *</label>
                    <input type="text" name="candidato_nombre" class="form-control" placeholder="Ej. Mariana Silva Torres" required maxlength="120">
                </div>

                <div class="row g-2">
                    <div class="col-7">
                        <label class="form-label small fw-semibold">Correo electrónico *</label>
                        <input type="email" name="candidato_email" class="form-control" placeholder="correo@ejemplo.com" required maxlength="120">
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-semibold">Teléfono *</label>
                        <input type="text" name="candidato_telefono" class="form-control" placeholder="3150000000" required maxlength="25">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Nivel aspirado</label>
                        <select name="nivel_interes_id" class="form-select">
                            <?php foreach ($listaNiveles as $niv): ?>
                                <option value="<?= (int) $niv['id'] ?>"><?= e((string) $niv['codigo'] . ' — ' . (string) $niv['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Modalidad</label>
                        <select name="modalidad" class="form-select">
                            <option value="VIRTUAL">Virtual (Meet)</option>
                            <option value="PRESENCIAL">Presencial</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Fecha y hora *</label>
                    <input type="datetime-local" name="fecha_hora" class="form-control"
                           value="<?= e(date('Y-m-d\T10:00', strtotime('+2 days'))) ?>" required>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Docente entrevistador asignado</label>
                    <select name="entrevistador_id" class="form-select">
                        <option value="0">— Por asignar —</option>
                        <?php foreach ($listaDocentes as $doc): ?>
                            <option value="<?= (int) $doc['id'] ?>"><?= e((string) $doc['nombre_completo']) ?> (<?= e((string) $doc['especialidad']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Enlace Meet o Salón</label>
                    <input type="text" name="enlace_o_lugar" class="form-control" placeholder="meet.google.com/ang-interview o Salón 101">
                </div>

                <div>
                    <label class="form-label small fw-semibold">Observaciones iniciales</label>
                    <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas previas sobre el aspirante..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-calendar-plus me-1"></i> Programar entrevista
                </button>
            </form>
        </div>
    </div>

    <!-- Listado y seguimiento de entrevistas -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h3 class="h5 fw-bold mb-0">Seguimiento de entrevistas programadas</h3>
                    <small class="text-muted">Coordina el estado y registra el nivel de clasificación obtenido</small>
                </div>

                <!-- Filtros por estado -->
                <div class="btn-group btn-group-sm">
                    <a href="<?= e(url('admin/entrevistas')) ?>" class="btn <?= $estadoActual === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todas</a>
                    <a href="<?= e(url('admin/entrevistas', ['estado' => 'PROGRAMADA'])) ?>" class="btn <?= $estadoActual === 'PROGRAMADA' ? 'btn-primary' : 'btn-outline-secondary' ?>">Programadas</a>
                    <a href="<?= e(url('admin/entrevistas', ['estado' => 'REALIZADA'])) ?>" class="btn <?= $estadoActual === 'REALIZADA' ? 'btn-primary' : 'btn-outline-secondary' ?>">Realizadas</a>
                    <a href="<?= e(url('admin/entrevistas', ['estado' => 'APROBADA'])) ?>" class="btn <?= $estadoActual === 'APROBADA' ? 'btn-primary' : 'btn-outline-secondary' ?>">Aprobadas</a>
                </div>
            </div>

            <?php if ($listaEntrevistas === []): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar2-x fs-1 text-muted"></i>
                    <p class="mt-2 mb-0 text-muted">No hay entrevistas registradas con el filtro seleccionado.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Candidato / Contacto</th>
                                <th>Fecha & Modalidad</th>
                                <th>Entrevistador</th>
                                <th>Estado</th>
                                <th>Nivel Sugerido</th>
                                <th class="text-end">Coordinar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listaEntrevistas as $ent): ?>
                                <?php
                                $entId = (int) $ent['id'];
                                $est = (string) $ent['estado'];
                                ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?= e((string) $ent['candidato_nombre']) ?></strong>
                                        <small class="text-muted d-block"><?= e((string) $ent['candidato_email']) ?> · <?= e((string) $ent['candidato_telefono']) ?></small>
                                        <?php if (!empty($ent['observaciones'])): ?>
                                            <small class="text-secondary d-block mt-1"><i class="bi bi-info-circle me-1"></i><?= e((string) $ent['observaciones']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <strong class="d-block"><?= e((string) $ent['fecha_hora']) ?></strong>
                                        <span class="badge bg-light text-dark border"><?= e((string) $ent['modalidad']) ?></span>
                                        <small class="d-block text-muted"><?= e((string) ($ent['enlace_o_lugar'] ?? '')) ?></small>
                                    </td>
                                    <td class="small">
                                        <?= e((string) ($ent['entrevistador_nombre'] ?? 'Sin asignar')) ?>
                                    </td>
                                    <td>
                                        <?php if ($est === 'APROBADA'): ?>
                                            <span class="badge bg-success-subtle text-success">Aprobada</span>
                                        <?php elseif ($est === 'REALIZADA'): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis">Realizada</span>
                                        <?php elseif ($est === 'PROGRAMADA'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Programada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary"><?= e($est) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($ent['resultado_nivel'])): ?>
                                            <span class="badge bg-primary"><?= e((string) $ent['resultado_nivel']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">Interés: <?= e((string) ($ent['nivel_codigo'] ?? 'A1')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEntrevista<?= $entId ?>">
                                            <i class="bi bi-sliders me-1"></i> Seguimiento
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de seguimiento de entrevista -->
                                <div class="modal fade" id="modalEntrevista<?= $entId ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="<?= e(url('admin/entrevistas/actualizar')) ?>" method="post" class="modal-content rounded-4 border-0 shadow">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="entrevista_id" value="<?= $entId ?>">

                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Seguimiento: <?= e((string) $ent['candidato_nombre']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Estado de la entrevista *</label>
                                                    <select name="estado" class="form-select">
                                                        <?php foreach (['PROGRAMADA', 'REALIZADA', 'APROBADA', 'CANCELADA', 'NO_ASISTIO'] as $op): ?>
                                                            <option value="<?= $op ?>" <?= $est === $op ? 'selected' : '' ?>><?= $op ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nivel sugerido tras evaluación (Resultado)</label>
                                                    <select name="resultado_nivel" class="form-select">
                                                        <option value="">— Sin definir aún —</option>
                                                        <?php foreach (['A1', 'A2', 'B1', 'B2', 'C1'] as $cod): ?>
                                                            <option value="<?= $cod ?>" <?= ($ent['resultado_nivel'] ?? '') === $cod ? 'selected' : '' ?>>Nivel <?= $cod ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Nueva nota de seguimiento</label>
                                                    <textarea name="observaciones" class="form-control" rows="2" placeholder="Resultado diagnóstico o acuerdos de matrícula..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
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
