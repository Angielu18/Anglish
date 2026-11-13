<?php
/**
 * Vista: Gestionar tareas (Módulo de Profesores).
 * Variables: $tareas, $cursos, $user.
 */
$listaTareas = is_array($tareas ?? null) ? $tareas : [];
$listaCursos = is_array($cursos ?? null) ? $cursos : [];
?>
<div class="row g-4">
    <!-- Formulario para crear nueva tarea -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <span class="badge bg-primary-subtle text-primary align-self-start mb-2">Nueva Asignación</span>
            <h3 class="h5 fw-bold mb-1">Crear y asignar tarea</h3>
            <p class="text-muted small mb-3">Define la actividad evaluativa, su ponderación y el curso o nivel al que va dirigida.</p>

            <form action="<?= e(url('profesor/tareas/crear')) ?>" method="post" class="d-grid gap-3">
                <?= csrf_field() ?>

                <div>
                    <label class="form-label small fw-semibold">Curso / Nivel destino *</label>
                    <select name="curso_id" class="form-select" required>
                        <?php foreach ($listaCursos as $cur): ?>
                            <option value="<?= (int) $cur['id'] ?>">
                                [<?= e((string) $cur['nivel_codigo']) ?>] <?= e((string) $cur['curso']) ?> (<?= e((string) $cur['codigo']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Título de la tarea o evaluación *</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Writing Essay: My Future Plans" required maxlength="100">
                </div>

                <div class="row g-2">
                    <div class="col-7">
                        <label class="form-label small fw-semibold">Tipo de actividad *</label>
                        <select name="tipo" class="form-select">
                            <option value="TALLER">Taller práctico</option>
                            <option value="QUIZ">Quiz</option>
                            <option value="PROYECTO">Proyecto / Speaking</option>
                            <option value="PARCIAL">Examen Parcial</option>
                            <option value="EXAMEN_FINAL">Examen Final</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-semibold">Peso (%) *</label>
                        <input type="number" name="porcentaje" class="form-control" value="15" min="1" max="100" step="1" required>
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Fecha y hora límite de entrega *</label>
                    <input type="datetime-local" name="fecha_entrega" class="form-control"
                           value="<?= e(date('Y-m-d\T23:59', strtotime('+7 days'))) ?>" required>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Instrucciones y criterios de evaluación</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                              placeholder="Detalla los objetivos, extensión requerida y rúbrica de calificación..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Publicar y asignar tarea
                </button>
            </form>
        </div>
    </div>

    <!-- Listado de tareas asignadas -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="h5 fw-bold mb-0">Tareas y evaluaciones asignadas</h3>
                    <small class="text-muted">Actividades programadas en tus cursos activos</small>
                </div>
                <a href="<?= e(url('profesor/revisar')) ?>" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-clipboard2-check me-1"></i> Ir a revisar entregas
                </a>
            </div>

            <?php if ($listaTareas === []): ?>
                <p class="text-muted small">No has creado tareas aún.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tarea / Actividad</th>
                                <th>Curso & Nivel</th>
                                <th>Peso</th>
                                <th>Fecha límite</th>
                                <th>Entregas / Calificados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listaTareas as $t): ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?= e((string) $t['nombre']) ?></strong>
                                        <span class="badge bg-secondary-subtle text-secondary small"><?= e((string) $t['tipo']) ?></span>
                                        <?php if (!empty($t['descripcion'])): ?>
                                            <small class="d-block text-muted mt-1"><?= e((string) $t['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= e((string) $t['nivel_codigo']) ?></span>
                                        <small class="d-block fw-semibold mt-1"><?= e((string) $t['curso_nombre']) ?></small>
                                        <small class="text-muted"><?= e((string) $t['curso_codigo']) ?></small>
                                    </td>
                                    <td><strong><?= e(porcentaje((float) $t['porcentaje'])) ?></strong></td>
                                    <td class="small"><?= e((string) $t['fecha_entrega']) ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            <?= (int) $t['total_entregas'] ?> entregas
                                        </span>
                                        <span class="badge bg-success-subtle text-success ms-1">
                                            <?= (int) $t['total_calificados'] ?> / <?= (int) $t['total_alumnos'] ?> calificados
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
