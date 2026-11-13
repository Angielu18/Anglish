<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Consultas de los tres tableros.
 *
 * Las vistas SQL (vw_progreso_estudiante, vw_asistencia_estudiante y
 * vw_estado_cuenta) concentran los cálculos de negocio. El modelo solo
 * reúne la información que necesita cada rol; la vista se limita a pintar.
 */
final class Dashboard extends Model
{
    /**
     * @return array<string, mixed>
     */
    public function admin(): array
    {
        $conteos = $this->fetchOne(
            "SELECT
                (SELECT COUNT(*) FROM estudiantes WHERE estado = 'ACTIVO') AS estudiantes,
                (SELECT COUNT(*) FROM profesores WHERE estado = 'ACTIVO') AS profesores,
                (SELECT COUNT(*) FROM cursos WHERE estado = 'EN_CURSO') AS cursos,
                (SELECT COUNT(*) FROM matriculas WHERE estado = 'ACTIVA') AS matriculas,
                (SELECT COUNT(*) FROM alertas WHERE leida = 0) AS alertas"
        ) ?? [];

        $periodo = date('Y-m');
        $ingreso = $this->fetchOne(
            "SELECT COALESCE(ingreso_total, 0) AS total
             FROM vw_ingresos_mensuales
             WHERE periodo = :periodo
             LIMIT 1",
            ['periodo' => $periodo]
        );

        $cartera = $this->fetchOne(
            "SELECT COUNT(*) AS cuotas, COALESCE(SUM(saldo), 0) AS saldo
             FROM vw_cartera_vencida"
        ) ?? [];

        $asistencia = $this->fetchOne(
            "SELECT ROUND(COALESCE(AVG(porcentaje_asistencia), 0), 1) AS promedio
             FROM vw_asistencia_estudiante"
        );

        return [
            'conteos' => [
                'estudiantes' => (int) ($conteos['estudiantes'] ?? 0),
                'profesores'  => (int) ($conteos['profesores'] ?? 0),
                'cursos'      => (int) ($conteos['cursos'] ?? 0),
                'matriculas'  => (int) ($conteos['matriculas'] ?? 0),
                'alertas'     => (int) ($conteos['alertas'] ?? 0),
            ],
            'periodo'             => $periodo,
            'recaudo_mes'         => (float) ($ingreso['total'] ?? 0),
            'cartera_total'       => (float) ($cartera['saldo'] ?? 0),
            'cartera_cuotas'      => (int) ($cartera['cuotas'] ?? 0),
            'asistencia_promedio' => (float) ($asistencia['promedio'] ?? 0),
            'cursos'              => $this->fetchAll(
                "SELECT codigo, curso, nivel_codigo, profesor, jornada, aula,
                        matriculados, cupo_maximo, cupo_disponible
                 FROM vw_cursos_detalle
                 ORDER BY matriculados DESC, curso ASC
                 LIMIT 6"
            ),
            'pagos'               => $this->fetchAll(
                "SELECT p.id, p.valor, p.fecha_pago, p.metodo_pago, p.estado,
                        CONCAT(u.nombres, ' ', u.apellidos) AS estudiante,
                        c.nombre AS curso
                 FROM pagos p
                 INNER JOIN cuotas q ON q.id = p.cuota_id
                 INNER JOIN matriculas m ON m.id = q.matricula_id
                 INNER JOIN estudiantes e ON e.id = m.estudiante_id
                 INNER JOIN usuarios u ON u.id = e.usuario_id
                 INNER JOIN cursos c ON c.id = m.curso_id
                 ORDER BY p.fecha_pago DESC
                 LIMIT 6"
            ),
            'alertas_tipo'        => $this->fetchAll(
                "SELECT tipo, COUNT(*) AS total
                 FROM alertas
                 WHERE leida = 0
                 GROUP BY tipo
                 ORDER BY total DESC"
            ),
            'auditoria'           => $this->fetchAll(
                "SELECT a.id, a.accion, a.modulo, a.descripcion, a.created_at,
                        COALESCE(CONCAT(u.nombres, ' ', u.apellidos), 'Sistema') AS usuario
                 FROM auditoria a
                 LEFT JOIN usuarios u ON u.id = a.usuario_id
                 ORDER BY a.created_at DESC
                 LIMIT 6"
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function professor(int $profesorId): array
    {
        // PDO con prepares nativos no permite repetir el mismo marcador
        // con nombre; por eso esta consulta usa parámetros posicionales.
        $stats = $this->fetchOne(
            "SELECT
                (SELECT COUNT(*) FROM cursos
                  WHERE profesor_id = ? AND estado = 'EN_CURSO') AS cursos,
                (SELECT COUNT(DISTINCT m.estudiante_id)
                   FROM matriculas m
                   INNER JOIN cursos c ON c.id = m.curso_id
                  WHERE c.profesor_id = ? AND m.estado = 'ACTIVA') AS estudiantes,
                (SELECT ROUND(COALESCE(AVG(v.porcentaje_asistencia), 0), 1)
                   FROM vw_asistencia_estudiante v
                   INNER JOIN matriculas m ON m.id = v.matricula_id
                   INNER JOIN cursos c ON c.id = m.curso_id
                  WHERE c.profesor_id = ? AND m.estado = 'ACTIVA') AS asistencia,
                (SELECT COUNT(*)
                   FROM evaluaciones e
                   INNER JOIN cursos c ON c.id = e.curso_id
                  WHERE c.profesor_id = ? AND e.estado <> 'CERRADA') AS evaluaciones",
            [$profesorId, $profesorId, $profesorId, $profesorId]
        ) ?? [];

        return [
            'stats' => [
                'cursos'      => (int) ($stats['cursos'] ?? 0),
                'estudiantes' => (int) ($stats['estudiantes'] ?? 0),
                'asistencia'  => (float) ($stats['asistencia'] ?? 0),
                'evaluaciones' => (int) ($stats['evaluaciones'] ?? 0),
            ],
            'cursos' => $this->fetchAll(
                "SELECT id, codigo, curso, nivel_codigo, jornada, aula,
                        matriculados, cupo_maximo
                 FROM vw_cursos_detalle
                 WHERE profesor_id = :profesor_id
                 ORDER BY curso ASC",
                ['profesor_id' => $profesorId]
            ),
            'sesiones' => $this->fetchAll(
                "SELECT s.id, s.fecha, s.hora_inicio, s.hora_fin, s.tema, s.estado,
                        c.nombre AS curso, c.codigo, c.aula
                 FROM sesiones_clase s
                 INNER JOIN cursos c ON c.id = s.curso_id
                 WHERE c.profesor_id = :profesor_id
                   AND s.fecha >= CURDATE()
                   AND s.estado = 'PROGRAMADA'
                 ORDER BY s.fecha ASC, s.hora_inicio ASC
                 LIMIT 5",
                ['profesor_id' => $profesorId]
            ),
            'evaluaciones' => $this->fetchAll(
                "SELECT e.id, e.nombre, e.tipo, e.porcentaje, e.fecha_entrega, e.estado,
                        c.nombre AS curso, c.codigo
                 FROM evaluaciones e
                 INNER JOIN cursos c ON c.id = e.curso_id
                 WHERE c.profesor_id = :profesor_id
                   AND e.estado <> 'CERRADA'
                 ORDER BY e.fecha_entrega ASC
                 LIMIT 5",
                ['profesor_id' => $profesorId]
            ),
            'riesgo' => $this->fetchAll(
                "SELECT v.matricula_id, v.estudiante, v.curso,
                        v.porcentaje_avance, v.promedio_ponderado,
                        COALESCE(a.porcentaje_asistencia, 0) AS porcentaje_asistencia
                 FROM vw_progreso_estudiante v
                 INNER JOIN matriculas m ON m.id = v.matricula_id
                 INNER JOIN cursos c ON c.id = m.curso_id
                 LEFT JOIN vw_asistencia_estudiante a ON a.matricula_id = v.matricula_id
                 WHERE c.profesor_id = :profesor_id
                   AND m.estado = 'ACTIVA'
                   AND (v.promedio_ponderado < :nota OR COALESCE(a.porcentaje_asistencia, 0) < :asistencia)
                 ORDER BY v.promedio_ponderado ASC, a.porcentaje_asistencia ASC
                 LIMIT 6",
                [
                    'profesor_id' => $profesorId,
                    'nota'        => (float) config('app.nota_minima', 3.0),
                    'asistencia'  => (float) config('app.asistencia_minima', 80.0),
                ]
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function student(int $estudianteId): array
    {
        $matricula = $this->fetchOne(
            "SELECT m.id AS matricula_id, m.curso_id, m.fecha_matricula,
                    m.valor_total, m.numero_cuotas,
                    c.codigo, c.nombre AS curso, c.jornada, c.aula,
                    c.fecha_inicio, c.fecha_fin,
                    n.codigo AS nivel_codigo, n.nombre AS nivel_nombre,
                    CONCAT(pu.nombres, ' ', pu.apellidos) AS profesor,
                    pu.email AS profesor_email,
                    p.especialidad
             FROM matriculas m
             INNER JOIN cursos c ON c.id = m.curso_id
             INNER JOIN niveles n ON n.id = c.nivel_id
             INNER JOIN profesores p ON p.id = c.profesor_id
             INNER JOIN usuarios pu ON pu.id = p.usuario_id
             WHERE m.estudiante_id = :estudiante_id
               AND m.estado = 'ACTIVA'
             ORDER BY m.id DESC
             LIMIT 1",
            ['estudiante_id' => $estudianteId]
        );

        if ($matricula === null) {
            return [
                'matricula' => null,
                'progreso' => null,
                'asistencia' => null,
                'cuenta' => null,
                'evaluaciones' => [],
                'alertas' => [],
                'asistencias' => [],
                'horarios' => [],
                'cuotas' => [],
                'resumen' => [
                    'progreso' => 0.0,
                    'promedio' => 0.0,
                    'asistencia' => 0.0,
                    'saldo' => 0.0,
                    'cartera' => 'SIN MATRÍCULA',
                    'alertas_sin_leer' => 0,
                ],
            ];
        }

        $matriculaId = (int) $matricula['matricula_id'];
        $cursoId = (int) $matricula['curso_id'];

        $progreso = $this->fetchOne(
            "SELECT * FROM vw_progreso_estudiante WHERE matricula_id = :id LIMIT 1",
            ['id' => $matriculaId]
        );

        $asistencia = $this->fetchOne(
            "SELECT * FROM vw_asistencia_estudiante WHERE matricula_id = :id LIMIT 1",
            ['id' => $matriculaId]
        );

        $cuenta = $this->fetchOne(
            "SELECT * FROM vw_estado_cuenta WHERE matricula_id = :id LIMIT 1",
            ['id' => $matriculaId]
        );

        $evaluaciones = $this->fetchAll(
            "SELECT e.id, e.nombre, e.tipo, e.porcentaje, e.fecha_entrega, e.estado,
                    cal.nota
             FROM evaluaciones e
             LEFT JOIN calificaciones cal
                    ON cal.evaluacion_id = e.id
                   AND cal.matricula_id = :matricula_id
             WHERE e.curso_id = :curso_id
               AND e.estado <> 'CERRADA'
               AND cal.id IS NULL
             ORDER BY e.fecha_entrega ASC
             LIMIT 6",
            [
                'matricula_id' => $matriculaId,
                'curso_id'     => $cursoId,
            ]
        );

        foreach ($evaluaciones as &$evaluacion) {
            $evaluacion['urgencia'] = urgencia_evento((string) $evaluacion['fecha_entrega']);
        }
        unset($evaluacion);

        $alertas = $this->fetchAll(
            "SELECT id, tipo, titulo, mensaje, fecha_evento, leida, created_at
             FROM alertas
             WHERE usuario_id = :usuario_id
             ORDER BY leida ASC, fecha_evento DESC
             LIMIT 6",
            ['usuario_id' => (int) \App\Core\Auth::id()]
        );

        foreach ($alertas as &$alerta) {
            $alerta['urgencia'] = urgencia_alerta((string) $alerta['tipo'], (string) $alerta['fecha_evento']);
        }
        unset($alerta);

        $asistencias = $this->fetchAll(
            "SELECT s.fecha, s.tema, s.hora_inicio, s.hora_fin, a.estado
             FROM asistencias a
             INNER JOIN sesiones_clase s ON s.id = a.sesion_id
             WHERE a.matricula_id = :matricula_id
             ORDER BY s.fecha DESC
             LIMIT 5",
            ['matricula_id' => $matriculaId]
        );

        $horarios = $this->fetchAll(
            "SELECT dia_semana, hora_inicio, hora_fin, aula
             FROM horarios
             WHERE curso_id = :curso_id
             ORDER BY dia_semana ASC, hora_inicio ASC",
            ['curso_id' => $cursoId]
        );

        $cuotas = $this->fetchAll(
            "SELECT q.id, q.numero, q.concepto, q.periodo, q.valor,
                    q.fecha_vencimiento, q.estado,
                    COALESCE(SUM(CASE WHEN p.estado = 'REGISTRADO' THEN p.valor ELSE 0 END), 0) AS pagado
             FROM cuotas q
             LEFT JOIN pagos p ON p.cuota_id = q.id
             WHERE q.matricula_id = :matricula_id
               AND q.estado <> 'ANULADA'
             GROUP BY q.id
             HAVING (q.valor - pagado) > 0
             ORDER BY q.fecha_vencimiento ASC
             LIMIT 3",
            ['matricula_id' => $matriculaId]
        );

        foreach ($cuotas as &$cuota) {
            $cuota['urgencia'] = urgencia_evento((string) $cuota['fecha_vencimiento'] . ' 23:59:59');
            $cuota['saldo'] = (float) $cuota['valor'] - (float) $cuota['pagado'];
        }
        unset($cuota);

        $sinLeer = (int) $this->scalar(
            "SELECT COUNT(*) FROM alertas WHERE usuario_id = :usuario_id AND leida = 0",
            ['usuario_id' => (int) \App\Core\Auth::id()]
        );

        return [
            'matricula'    => $matricula,
            'progreso'     => $progreso,
            'asistencia'   => $asistencia,
            'cuenta'       => $cuenta,
            'evaluaciones' => $evaluaciones,
            'alertas'      => $alertas,
            'asistencias'  => $asistencias,
            'horarios'     => $horarios,
            'cuotas'       => $cuotas,
            'resumen'      => [
                'progreso'        => (float) ($progreso['porcentaje_avance'] ?? 0),
                'promedio'        => (float) ($progreso['promedio_ponderado'] ?? 0),
                'asistencia'      => (float) ($asistencia['porcentaje_asistencia'] ?? 0),
                'saldo'           => (float) ($cuenta['saldo'] ?? 0),
                'cartera'         => (string) ($cuenta['estado_cartera'] ?? 'SIN MATRÍCULA'),
                'alertas_sin_leer' => $sinLeer,
            ],
        ];
    }

    /**
     * Marca como leídas todas las alertas del usuario autenticado.
     * El filtro por usuario_id impide que un estudiante altere alertas ajenas.
     */
    public function markAlertsRead(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            "UPDATE alertas SET leida = 1 WHERE usuario_id = :usuario_id AND leida = 0"
        );
        $stmt->execute(['usuario_id' => $usuarioId]);

        return $stmt->rowCount();
    }
}
