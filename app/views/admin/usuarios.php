<?php
/**
 * Vista: Gestión de acceso al sistema (Módulo de Administrador).
 * Variables: $usuarios, $roles, $permisos, $filtroRol, $filtroEstado, $user.
 */
$listaUsuarios = is_array($usuarios ?? null) ? $usuarios : [];
$listaRoles = is_array($roles ?? null) ? $roles : [];
$listaPermisos = is_array($permisos ?? null) ? $permisos : [];
$rolActual = (string) ($filtroRol ?? '');
?>
<div class="row g-4">
    <!-- Formulario para registrar nuevo usuario con rol -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <span class="badge bg-primary-subtle text-primary align-self-start mb-2">Control de Acceso (RBAC)</span>
            <h3 class="h5 fw-bold mb-1">Registrar nuevo usuario</h3>
            <p class="text-muted small mb-3">Crea cuentas con credenciales formales y asigna su rol institucional.</p>

            <form action="<?= e(url('admin/usuarios/crear')) ?>" method="post" class="d-grid gap-3">
                <?= csrf_field() ?>

                <div>
                    <label class="form-label small fw-semibold">Rol en el sistema *</label>
                    <select name="rol_id" class="form-select" required>
                        <?php foreach ($listaRoles as $r): ?>
                            <option value="<?= (int) $r['id'] ?>" <?= (int) $r['id'] === 3 ? 'selected' : '' ?>>
                                <?= e((string) $r['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" placeholder="Ej. Carlos" required maxlength="80">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" placeholder="Ej. Ramírez" required maxlength="80">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Documento *</label>
                        <input type="text" name="documento" class="form-control" placeholder="1000123456" required maxlength="20">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="3100000000" maxlength="20">
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold">Correo institucional *</label>
                    <input type="email" name="email" class="form-control" placeholder="nombre.apellido@anglish.edu.co" required maxlength="120">
                </div>

                <div>
                    <label class="form-label small fw-semibold">Contraseña inicial (mín. 8 caracteres) *</label>
                    <input type="password" name="password" class="form-control" placeholder="Contraseña segura" required minlength="8" maxlength="72">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-person-plus-fill me-1"></i> Crear cuenta y otorgar acceso
                </button>
            </form>
        </div>

        <!-- Resumen de Roles y Permisos -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mt-4">
            <h4 class="h6 fw-bold mb-2"><i class="bi bi-key-fill text-primary me-1"></i>Matriz de Roles del Sistema</h4>
            <ul class="list-unstyled small mb-0 d-grid gap-2">
                <?php foreach ($listaRoles as $r): ?>
                    <li class="p-2 border rounded-3">
                        <strong class="d-block text-primary"><?= e((string) $r['nombre']) ?></strong>
                        <span class="text-muted"><?= e((string) $r['descripcion']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Tabla de administración de usuarios y accesos -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h3 class="h5 fw-bold mb-0">Directorio de Usuarios y Control de Estado</h3>
                    <small class="text-muted">Administra roles, activa/inactiva cuentas y desbloquea accesos</small>
                </div>

                <div class="btn-group btn-group-sm">
                    <a href="<?= e(url('admin/usuarios')) ?>" class="btn <?= $rolActual === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todos</a>
                    <a href="<?= e(url('admin/usuarios', ['rol' => 'ADMINISTRADOR'])) ?>" class="btn <?= $rolActual === 'ADMINISTRADOR' ? 'btn-primary' : 'btn-outline-secondary' ?>">Admins</a>
                    <a href="<?= e(url('admin/usuarios', ['rol' => 'PROFESOR'])) ?>" class="btn <?= $rolActual === 'PROFESOR' ? 'btn-primary' : 'btn-outline-secondary' ?>">Profesores</a>
                    <a href="<?= e(url('admin/usuarios', ['rol' => 'ESTUDIANTE'])) ?>" class="btn <?= $rolActual === 'ESTUDIANTE' ? 'btn-primary' : 'btn-outline-secondary' ?>">Estudiantes</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Documento</th>
                            <th>Rol Asignado</th>
                            <th>Estado</th>
                            <th>Último acceso</th>
                            <th class="text-end">Administrar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaUsuarios as $u): ?>
                            <?php
                            $uid = (int) $u['id'];
                            $est = (string) $u['estado'];
                            $rolNombre = (string) $u['rol'];
                            ?>
                            <tr>
                                <td>
                                    <strong class="d-block"><?= e((string) $u['nombres'] . ' ' . (string) $u['apellidos']) ?></strong>
                                    <small class="text-muted"><?= e((string) $u['email']) ?></small>
                                </td>
                                <td class="small"><?= e((string) $u['documento']) ?></td>
                                <td>
                                    <?php if ($rolNombre === 'ADMINISTRADOR'): ?>
                                        <span class="badge bg-dark">Administrador</span>
                                    <?php elseif ($rolNombre === 'PROFESOR'): ?>
                                        <span class="badge bg-primary">Profesor</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Estudiante</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($est === 'ACTIVO'): ?>
                                        <span class="badge bg-success-subtle text-success">Activo</span>
                                    <?php elseif ($est === 'BLOQUEADO'): ?>
                                        <span class="badge bg-danger-subtle text-danger">Bloqueado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= e((string) ($u['ultimo_login'] ?? 'Nunca')) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalUsuario<?= $uid ?>">
                                        <i class="bi bi-gear"></i> Permisos
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal para cambiar rol y estado del usuario -->
                            <div class="modal fade" id="modalUsuario<?= $uid ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <form action="<?= e(url('admin/usuarios/actualizar')) ?>" method="post" class="modal-content rounded-4 border-0 shadow">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="usuario_id" value="<?= $uid ?>">

                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title h6 fw-bold">Acceso: <?= e((string) $u['nombres'] . ' ' . (string) $u['apellidos']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Rol del usuario</label>
                                                <select name="rol_id" class="form-select">
                                                    <?php foreach ($listaRoles as $r): ?>
                                                        <option value="<?= (int) $r['id'] ?>" <?= (int) $u['rol_id'] === (int) $r['id'] ? 'selected' : '' ?>>
                                                            <?= e((string) $r['nombre']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold">Estado de la cuenta</label>
                                                <select name="estado" class="form-select">
                                                    <option value="ACTIVO" <?= $est === 'ACTIVO' ? 'selected' : '' ?>>ACTIVO (Habilitado)</option>
                                                    <option value="INACTIVO" <?= $est === 'INACTIVO' ? 'selected' : '' ?>>INACTIVO (Suspendido)</option>
                                                    <option value="BLOQUEADO" <?= $est === 'BLOQUEADO' ? 'selected' : '' ?>>BLOQUEADO</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
