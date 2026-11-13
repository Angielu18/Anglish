<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Consultas relacionadas con usuarios, roles y perfiles.
 */
final class User extends Model
{
    /**
     * Devuelve los datos necesarios para autenticar y construir la sesión.
     *
     * @return array<string, mixed>|null
     */
    public function findForAuth(string $email): ?array
    {
        return $this->fetchOne(
            "SELECT u.id,
                    u.rol_id,
                    u.nombres,
                    u.apellidos,
                    u.email,
                    u.password_hash,
                    u.estado,
                    u.intentos_fallidos,
                    u.bloqueado_hasta,
                    r.nombre AS rol,
                    e.id AS estudiante_id,
                    p.id AS profesor_id,
                    (
                        SELECT n.codigo
                        FROM matriculas m
                        INNER JOIN cursos c ON c.id = m.curso_id
                        INNER JOIN niveles n ON n.id = c.nivel_id
                        WHERE m.estudiante_id = e.id
                          AND m.estado = 'ACTIVA'
                        ORDER BY m.id DESC
                        LIMIT 1
                    ) AS nivel_codigo
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             LEFT JOIN estudiantes e ON e.usuario_id = u.id
             LEFT JOIN profesores p ON p.usuario_id = u.id
             WHERE u.email = :email
             LIMIT 1",
            ['email' => $email]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT u.id, u.nombres, u.apellidos, u.email, u.telefono,
                    u.estado, r.nombre AS rol
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.id = :id
             LIMIT 1",
            ['id' => $id]
        );
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET ultimo_login = NOW(), updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    public function recordFailedAttempt(int $id, int $maxIntentos, int $bloqueoMinutos): void
    {
        $bloqueadoHasta = date('Y-m-d H:i:s', time() + ($bloqueoMinutos * 60));

        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET intentos_fallidos = intentos_fallidos + 1,
                 bloqueado_hasta = CASE
                     WHEN intentos_fallidos + 1 >= :maximo THEN :bloqueado
                     ELSE bloqueado_hasta
                 END,
                 updated_at = NOW()
             WHERE id = :id"
        );

        $stmt->execute([
            'id'        => $id,
            'maximo'    => $maxIntentos,
            'bloqueado' => $bloqueadoHasta,
        ]);
    }

    public function clearFailedAttempts(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET intentos_fallidos = 0, bloqueado_hasta = NULL, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET password_hash = :hash, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    /**
     * Lista todos los usuarios para el panel de administración de accesos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(?string $rol = null, ?string $estado = null): array
    {
        $sql = "SELECT u.id,
                       u.rol_id,
                       u.nombres,
                       u.apellidos,
                       u.documento,
                       u.email,
                       u.telefono,
                       u.estado,
                       u.intentos_fallidos,
                       u.ultimo_login,
                       u.created_at,
                       r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE 1=1";

        $params = [];
        if ($rol !== null && $rol !== '') {
            $sql .= " AND r.nombre = :rol";
            $params['rol'] = strtoupper($rol);
        }

        if ($estado !== null && $estado !== '') {
            $sql .= " AND u.estado = :estado";
            $params['estado'] = strtoupper($estado);
        }

        $sql .= " ORDER BY u.id ASC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarRoles(): array
    {
        return $this->fetchAll("SELECT id, nombre, descripcion FROM roles ORDER BY id ASC");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPermisosPorRol(): array
    {
        return $this->fetchAll(
            "SELECT r.nombre AS rol, p.clave, p.descripcion, p.modulo
             FROM rol_permiso rp
             INNER JOIN roles r ON r.id = rp.rol_id
             INNER JOIN permisos p ON p.id = rp.permiso_id
             ORDER BY r.id ASC, p.modulo ASC"
        );
    }

    /**
     * Crea un usuario y su perfil asociado (profesor o estudiante) si corresponde.
     */
    public function crearUsuario(
        int $rolId,
        string $nombres,
        string $apellidos,
        string $documento,
        string $email,
        string $password,
        ?string $telefono
    ): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (rol_id, nombres, apellidos, documento, email, password_hash, telefono, estado)
             VALUES (:rol_id, :nombres, :apellidos, :documento, :email, :hash, :telefono, 'ACTIVO')"
        );

        $stmt->execute([
            'rol_id'    => $rolId,
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'documento' => $documento,
            'email'     => mb_strtolower(trim($email)),
            'hash'      => $hash,
            'telefono'  => $telefono,
        ]);

        $usuarioId = (int) $this->db->lastInsertId();

        // Si es profesor (rol_id = 2), crear registro en profesores
        if ($rolId === 2) {
            $stmtProf = $this->db->prepare(
                "INSERT INTO profesores (usuario_id, especialidad, titulo, fecha_contratacion, estado)
                 VALUES (:uid, 'Inglés General', 'Docente de Inglés', CURDATE(), 'ACTIVO')"
            );
            $stmtProf->execute(['uid' => $usuarioId]);
        } elseif ($rolId === 3) {
            // Si es estudiante (rol_id = 3), crear registro en estudiantes
            $codigo = 'EST-' . date('Y') . '-' . str_pad((string) $usuarioId, 3, '0', STR_PAD_LEFT);
            $stmtEst = $this->db->prepare(
                "INSERT INTO estudiantes (usuario_id, codigo, fecha_nacimiento, fecha_ingreso, estado)
                 VALUES (:uid, :codigo, '2000-01-01', CURDATE(), 'ACTIVO')"
            );
            $stmtEst->execute(['uid' => $usuarioId, 'codigo' => $codigo]);
        }

        return $usuarioId;
    }

    /**
     * Actualiza el estado y rol de un usuario y limpia bloqueos si se reactiva.
     */
    public function actualizarAcceso(int $id, int $rolId, string $estado): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET rol_id = :rol_id,
                 estado = :estado,
                 intentos_fallidos = CASE WHEN :estado2 = 'ACTIVO' THEN 0 ELSE intentos_fallidos END,
                 bloqueado_hasta = CASE WHEN :estado3 = 'ACTIVO' THEN NULL ELSE bloqueado_hasta END,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id'      => $id,
            'rol_id'  => $rolId,
            'estado'  => $estado,
            'estado2' => $estado,
            'estado3' => $estado,
        ]);
    }
}
