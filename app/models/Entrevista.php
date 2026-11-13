<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo para la gestión de entrevistas de nivelación e ingreso.
 */
final class Entrevista extends Model
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodas(?string $filtroEstado = null): array
    {
        $sql = "SELECT e.id,
                       e.candidato_nombre,
                       e.candidato_email,
                       e.candidato_telefono,
                       e.nivel_interes_id,
                       n.codigo AS nivel_codigo,
                       n.nombre AS nivel_nombre,
                       e.fecha_hora,
                       e.modalidad,
                       e.enlace_o_lugar,
                       e.estado,
                       e.resultado_nivel,
                       e.observaciones,
                       e.entrevistador_id,
                       CONCAT(u.nombres, ' ', u.apellidos) AS entrevistador_nombre
                FROM entrevistas e
                LEFT JOIN niveles n ON n.id = e.nivel_interes_id
                LEFT JOIN usuarios u ON u.id = e.entrevistador_id";

        $params = [];
        if ($filtroEstado !== null && $filtroEstado !== '') {
            $sql .= " WHERE e.estado = :estado";
            $params['estado'] = $filtroEstado;
        }

        $sql .= " ORDER BY e.fecha_hora DESC";

        return $this->fetchAll($sql, $params);
    }

    public function crear(
        string $nombre,
        string $email,
        string $telefono,
        ?int $nivelId,
        string $fechaHora,
        string $modalidad,
        ?string $enlaceLugar,
        ?int $entrevistadorId,
        ?string $observaciones
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO entrevistas
             (candidato_nombre, candidato_email, candidato_telefono, nivel_interes_id,
              fecha_hora, modalidad, enlace_o_lugar, estado, entrevistador_id, observaciones)
             VALUES
             (:nombre, :email, :tel, :nivel_id, :fecha_hora, :modalidad, :lugar, 'PROGRAMADA', :entrevistador_id, :obs)"
        );

        $stmt->execute([
            'nombre'           => $nombre,
            'email'            => $email,
            'tel'              => $telefono,
            'nivel_id'         => $nivelId ?: null,
            'fecha_hora'       => $fechaHora,
            'modalidad'        => $modalidad,
            'lugar'            => $enlaceLugar,
            'entrevistador_id' => $entrevistadorId ?: null,
            'obs'              => $observaciones,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizarEstado(
        int $id,
        string $estado,
        ?string $resultadoNivel,
        ?string $observaciones
    ): bool {
        $stmt = $this->db->prepare(
            "UPDATE entrevistas
             SET estado = :estado,
                 resultado_nivel = COALESCE(:resultado_nivel, resultado_nivel),
                 observaciones = CONCAT_WS(' | ', observaciones, :obs),
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            'id'              => $id,
            'estado'          => $estado,
            'resultado_nivel' => $resultadoNivel,
            'obs'             => $observaciones,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarNiveles(): array
    {
        return $this->fetchAll("SELECT id, codigo, nombre, descripcion FROM niveles ORDER BY orden ASC");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarDocentes(): array
    {
        return $this->fetchAll(
            "SELECT u.id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo, p.especialidad
             FROM usuarios u
             INNER JOIN profesores p ON p.usuario_id = u.id
             WHERE u.estado = 'ACTIVO'
             ORDER BY u.nombres ASC"
        );
    }
}
