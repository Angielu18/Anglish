<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modelo para la gestión de tareas, entregas de trabajos y calificaciones.
 */
final class Tarea extends Model
{
    /**
     * Lista las evaluaciones asignadas a los cursos donde el estudiante está matriculado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorEstudiante(int $estudianteId): array
    {
        $sql = "SELECT ev.id AS evaluacion_id,
                       ev.nombre AS evaluacion_nombre,
                       ev.tipo,
                       ev.porcentaje,
                       ev.fecha_entrega,
                       ev.descripcion,
                       c.id AS curso_id,
                       c.nombre AS curso_nombre,
                       c.codigo AS curso_codigo,
                       n.codigo AS nivel_codigo,
                       m.id AS matricula_id,
                       et.id AS entrega_id,
                       et.archivo_url,
                       et.archivo_nombre,
                       et.comentario_estudiante,
                       et.fecha_entrega AS fecha_enviado,
                       et.estado AS estado_entrega,
                       et.retroalimentacion,
                       cal.nota,
                       cal.observacion AS nota_observacion
                FROM matriculas m
                INNER JOIN cursos c ON c.id = m.curso_id
                INNER JOIN niveles n ON n.id = c.nivel_id
                INNER JOIN evaluaciones ev ON ev.curso_id = c.id
                LEFT JOIN entregas_tareas et ON et.evaluacion_id = ev.id AND et.matricula_id = m.id
                LEFT JOIN calificaciones cal ON cal.evaluacion_id = ev.id AND cal.matricula_id = m.id
                WHERE m.estudiante_id = :estudiante_id
                  AND m.estado = 'ACTIVA'
                ORDER BY ev.fecha_entrega ASC";

        return $this->fetchAll($sql, ['estudiante_id' => $estudianteId]);
    }

    /**
     * Registra o actualiza la entrega de un trabajo por parte del estudiante.
     */
    public function guardarEntrega(
        int $evaluacionId,
        int $matriculaId,
        ?string $archivoUrl,
        ?string $archivoNombre,
        ?string $comentario
    ): bool {
        // Verificar si ya existe entrega
        $existe = $this->fetchOne(
            "SELECT id FROM entregas_tareas WHERE evaluacion_id = :ev_id AND matricula_id = :m_id",
            ['ev_id' => $evaluacionId, 'm_id' => $matriculaId]
        );

        if ($existe !== null) {
            $stmt = $this->db->prepare(
                "UPDATE entregas_tareas
                 SET archivo_url = COALESCE(:archivo_url, archivo_url),
                     archivo_nombre = COALESCE(:archivo_nombre, archivo_nombre),
                     comentario_estudiante = :comentario,
                     fecha_entrega = NOW(),
                     estado = 'ENTREGADA',
                     updated_at = NOW()
                 WHERE id = :id"
            );
            return $stmt->execute([
                'id'             => (int) $existe['id'],
                'archivo_url'    => $archivoUrl,
                'archivo_nombre' => $archivoNombre,
                'comentario'     => $comentario,
            ]);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO entregas_tareas
             (evaluacion_id, matricula_id, archivo_url, archivo_nombre, comentario_estudiante, fecha_entrega, estado)
             VALUES
             (:ev_id, :m_id, :archivo_url, :archivo_nombre, :comentario, NOW(), 'ENTREGADA')"
        );

        return $stmt->execute([
            'ev_id'          => $evaluacionId,
            'm_id'           => $matriculaId,
            'archivo_url'    => $archivoUrl,
            'archivo_nombre' => $archivoNombre,
            'comentario'     => $comentario,
        ]);
    }

    /**
     * Lista las tareas creadas por o asignadas a los cursos de un profesor.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorProfesor(int $profesorId): array
    {
        $sql = "SELECT ev.id,
                       ev.nombre,
                       ev.tipo,
                       ev.porcentaje,
                       ev.fecha_entrega,
                       ev.descripcion,
                       ev.estado,
                       c.id AS curso_id,
                       c.codigo AS curso_codigo,
                       c.nombre AS curso_nombre,
                       n.codigo AS nivel_codigo,
                       (SELECT COUNT(*) FROM matriculas m WHERE m.curso_id = c.id AND m.estado = 'ACTIVA') AS total_alumnos,
                       (SELECT COUNT(*) FROM entregas_tareas et WHERE et.evaluacion_id = ev.id) AS total_entregas,
                       (SELECT COUNT(*) FROM calificaciones cal WHERE cal.evaluacion_id = ev.id) AS total_calificados
                FROM evaluaciones ev
                INNER JOIN cursos c ON c.id = ev.curso_id
                INNER JOIN niveles n ON n.id = c.nivel_id
                WHERE c.profesor_id = :profesor_id
                ORDER BY ev.fecha_entrega DESC";

        return $this->fetchAll($sql, ['profesor_id' => $profesorId]);
    }

    /**
     * Lista las entregas de estudiantes pendientes o realizadas para cursos del docente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarEntregasParaProfesor(int $profesorId): array
    {
        $sql = "SELECT et.id AS entrega_id,
                       et.evaluacion_id,
                       et.matricula_id,
                       et.archivo_url,
                       et.archivo_nombre,
                       et.comentario_estudiante,
                       et.fecha_entrega AS fecha_envio,
                       et.estado AS estado_entrega,
                       et.retroalimentacion,
                       ev.nombre AS evaluacion_nombre,
                       ev.tipo AS evaluacion_tipo,
                       ev.porcentaje,
                       ev.fecha_entrega AS fecha_limite,
                       c.id AS curso_id,
                       c.nombre AS curso_nombre,
                       c.codigo AS curso_codigo,
                       CONCAT(u.nombres, ' ', u.apellidos) AS estudiante_nombre,
                       u.email AS estudiante_email,
                       e.codigo AS estudiante_codigo,
                       cal.id AS calificacion_id,
                       cal.nota,
                       cal.observacion AS nota_observacion
                FROM entregas_tareas et
                INNER JOIN evaluaciones ev ON ev.id = et.evaluacion_id
                INNER JOIN matriculas m ON m.id = et.matricula_id
                INNER JOIN estudiantes e ON e.id = m.estudiante_id
                INNER JOIN usuarios u ON u.id = e.usuario_id
                INNER JOIN cursos c ON c.id = ev.curso_id
                LEFT JOIN calificaciones cal ON cal.evaluacion_id = ev.id AND cal.matricula_id = m.id
                WHERE c.profesor_id = :profesor_id
                ORDER BY et.fecha_entrega DESC";

        return $this->fetchAll($sql, ['profesor_id' => $profesorId]);
    }

    /**
     * Califica una entrega y registra la nota formal en la tabla calificaciones.
     */
    public function calificarEntrega(
        int $entregaId,
        float $nota,
        ?string $retroalimentacion,
        int $usuarioId
    ): bool {
        $entrega = $this->fetchOne(
            "SELECT evaluacion_id, matricula_id FROM entregas_tareas WHERE id = :id",
            ['id' => $entregaId]
        );

        if ($entrega === null) {
            return false;
        }

        $evaluacionId = (int) $entrega['evaluacion_id'];
        $matriculaId  = (int) $entrega['matricula_id'];

        // Insertar o actualizar calificación
        $calExiste = $this->fetchOne(
            "SELECT id FROM calificaciones WHERE evaluacion_id = :ev_id AND matricula_id = :m_id",
            ['ev_id' => $evaluacionId, 'm_id' => $matriculaId]
        );

        $calId = null;
        if ($calExiste !== null) {
            $calId = (int) $calExiste['id'];
            $stmt = $this->db->prepare(
                "UPDATE calificaciones
                 SET nota = :nota, observacion = :obs, registrado_por = :reg_por, updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([
                'id'      => $calId,
                'nota'    => $nota,
                'obs'     => $retroalimentacion,
                'reg_por' => $usuarioId,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO calificaciones (evaluacion_id, matricula_id, nota, observacion, registrado_por)
                 VALUES (:ev_id, :m_id, :nota, :obs, :reg_por)"
            );
            $stmt->execute([
                'ev_id'   => $evaluacionId,
                'm_id'    => $matriculaId,
                'nota'    => $nota,
                'obs'     => $retroalimentacion,
                'reg_por' => $usuarioId,
            ]);
            $calId = (int) $this->db->lastInsertId();
        }

        // Actualizar entrega
        $stmt2 = $this->db->prepare(
            "UPDATE entregas_tareas
             SET estado = 'CALIFICADA',
                 retroalimentacion = :retro,
                 calificacion_id = :cal_id,
                 updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt2->execute([
            'id'     => $entregaId,
            'retro'  => $retroalimentacion,
            'cal_id' => $calId,
        ]);
    }

    /**
     * Crea una nueva tarea/evaluación para un curso.
     */
    public function crearTarea(
        int $cursoId,
        string $nombre,
        string $tipo,
        float $porcentaje,
        string $fechaEntrega,
        ?string $descripcion,
        int $creadoPor
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO evaluaciones
             (curso_id, nombre, tipo, porcentaje, fecha_entrega, descripcion, estado, creado_por)
             VALUES
             (:curso_id, :nombre, :tipo, :porcentaje, :fecha_entrega, :descripcion, 'ABIERTA', :creado_por)"
        );

        $stmt->execute([
            'curso_id'      => $cursoId,
            'nombre'        => $nombre,
            'tipo'          => $tipo,
            'porcentaje'    => $porcentaje,
            'fecha_entrega' => $fechaEntrega,
            'descripcion'   => $descripcion,
            'creado_por'    => $creadoPor,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Reporte consolidado de un estudiante (calificaciones, asistencias y pagos).
     *
     * @return array<string, mixed>
     */
    public function obtenerReporteEstudiante(int $estudianteId): array
    {
        $progreso = $this->fetchOne(
            "SELECT * FROM vw_progreso_estudiante WHERE estudiante_id = :id LIMIT 1",
            ['id' => $estudianteId]
        );

        $asistencia = $this->fetchOne(
            "SELECT * FROM vw_asistencia_estudiante WHERE estudiante_id = :id LIMIT 1",
            ['id' => $estudianteId]
        );

        $calificaciones = $this->fetchAll(
            "SELECT ev.nombre AS evaluacion, ev.tipo, ev.porcentaje, ev.fecha_entrega,
                    c.nota, c.observacion, cur.nombre AS curso
             FROM calificaciones c
             INNER JOIN evaluaciones ev ON ev.id = c.evaluacion_id
             INNER JOIN matriculas m ON m.id = c.matricula_id
             INNER JOIN cursos cur ON cur.id = m.curso_id
             WHERE m.estudiante_id = :id
             ORDER BY ev.fecha_entrega DESC",
            ['id' => $estudianteId]
        );

        $asistenciasDetalle = $this->fetchAll(
            "SELECT s.fecha, s.tema, a.estado, a.observacion, cur.nombre AS curso
             FROM asistencias a
             INNER JOIN sesiones_clase s ON s.id = a.sesion_id
             INNER JOIN matriculas m ON m.id = a.matricula_id
             INNER JOIN cursos cur ON cur.id = m.curso_id
             WHERE m.estudiante_id = :id
             ORDER BY s.fecha DESC
             LIMIT 15",
            ['id' => $estudianteId]
        );

        return [
            'progreso'            => $progreso,
            'asistencia'          => $asistencia,
            'calificaciones'      => $calificaciones,
            'asistencias_detalle' => $asistenciasDetalle,
        ];
    }

    /**
     * Reporte consolidado para profesores (promedio de cursos, rendimiento y estudiantes en riesgo).
     *
     * @return array<string, mixed>
     */
    public function obtenerReporteProfesor(int $profesorId): array
    {
        $cursos = $this->fetchAll(
            "SELECT c.id, c.codigo, c.nombre, n.codigo AS nivel,
                    (SELECT COUNT(*) FROM matriculas m WHERE m.curso_id = c.id AND m.estado = 'ACTIVA') AS matriculados,
                    (SELECT ROUND(AVG(cal.nota), 2)
                     FROM calificaciones cal
                     INNER JOIN evaluaciones ev ON ev.id = cal.evaluacion_id
                     WHERE ev.curso_id = c.id) AS promedio_curso
             FROM cursos c
             INNER JOIN niveles n ON n.id = c.nivel_id
             WHERE c.profesor_id = :profesor_id",
            ['profesor_id' => $profesorId]
        );

        $estudiantesRiesgo = $this->fetchAll(
            "SELECT e.codigo, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo,
                    u.email, c.nombre AS curso,
                    p.promedio_ponderado, a.porcentaje_asistencia
             FROM matriculas m
             INNER JOIN cursos c ON c.id = m.curso_id
             INNER JOIN estudiantes e ON e.id = m.estudiante_id
             INNER JOIN usuarios u ON u.id = e.usuario_id
             LEFT JOIN vw_progreso_estudiante p ON p.matricula_id = m.id
             LEFT JOIN vw_asistencia_estudiante a ON a.matricula_id = m.id
             WHERE c.profesor_id = :profesor_id
               AND (p.promedio_ponderado < 3.0 OR a.porcentaje_asistencia < 80.0)
             ORDER BY p.promedio_ponderado ASC",
            ['profesor_id' => $profesorId]
        );

        return [
            'cursos'             => $cursos,
            'estudiantes_riesgo' => $estudiantesRiesgo,
        ];
    }
}
