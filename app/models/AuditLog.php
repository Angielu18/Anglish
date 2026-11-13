<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Logger;
use App\Core\Request;
use Throwable;

/**
 * Registro de auditoría funcional.
 *
 * Se escribe en la tabla auditoria. Si la auditoría falla, la operación
 * principal no se debe romper: se deja la traza en el log técnico.
 */
final class AuditLog extends Model
{
    public function record(
        ?int $usuarioId,
        string $accion,
        string $modulo,
        ?string $descripcion = null
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO auditoria
                    (usuario_id, accion, modulo, descripcion, ip, user_agent)
                 VALUES
                    (:usuario_id, :accion, :modulo, :descripcion, :ip, :user_agent)"
            );

            $stmt->execute([
                'usuario_id'  => $usuarioId,
                'accion'      => mb_substr($accion, 0, 30, 'UTF-8'),
                'modulo'      => mb_substr($modulo, 0, 40, 'UTF-8'),
                'descripcion' => $descripcion !== null
                    ? mb_substr($descripcion, 0, 255, 'UTF-8')
                    : null,
                'ip'          => Request::ip(),
                'user_agent'  => Request::userAgent(),
            ]);
        } catch (Throwable $e) {
            Logger::warning('No se pudo escribir la auditoría', [
                'accion' => $accion,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
