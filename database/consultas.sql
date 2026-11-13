-- =====================================================================
-- ANGLISH — Consultas de operación y reportes
-- Ejecutar con: mysql -u root -D anglish < database/consultas.sql
-- Las variables @... son valores de ejemplo ejecutables.
-- En PHP se reemplazan por marcadores :nombre y se vinculan con PDO
-- (nunca se concatena entrada del usuario).
-- =====================================================================

USE anglish;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @estudiante_id = 1;
SET @usuario_id = 6;
SET @profesor_id = 1;
SET @desde = '2026-09-01 00:00:00';
SET @hasta = '2026-11-09 00:00:00';
SET @modulo = 'Pagos';

-- ---------------------------------------------------------------------
-- 1. Dashboard del administrador (una sola consulta, sin N+1)
-- ---------------------------------------------------------------------
SELECT
    (SELECT COUNT(*) FROM estudiantes WHERE estado = 'ACTIVO') AS estudiantes_activos,
    (SELECT COUNT(*) FROM profesores WHERE estado = 'ACTIVO') AS profesores_activos,
    (SELECT COUNT(*) FROM cursos WHERE estado = 'EN_CURSO') AS cursos_en_curso,
    (SELECT COUNT(*) FROM matriculas WHERE estado = 'ACTIVA') AS matriculas_activas,
    (SELECT COALESCE(SUM(valor), 0) FROM pagos
      WHERE estado = 'REGISTRADO'
        AND DATE_FORMAT(fecha_pago, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS ingresos_mes,
    (SELECT COALESCE(SUM(saldo), 0) FROM vw_estado_cuenta
      WHERE estado_matricula = 'ACTIVA') AS cartera_pendiente,
    (SELECT COALESCE(SUM(saldo), 0) FROM vw_cartera_vencida) AS cartera_vencida,
    (SELECT COALESCE(ROUND(AVG(porcentaje_asistencia), 1), 0)
       FROM vw_asistencia_estudiante WHERE sesiones_totales > 0) AS asistencia_promedio,
    (SELECT COUNT(*) FROM alertas WHERE leida = 0) AS alertas_no_leidas;

-- ---------------------------------------------------------------------
-- 2. Estudiantes activos por nivel MCER
-- ---------------------------------------------------------------------
SELECT
    n.codigo AS nivel,
    n.nombre,
    COUNT(DISTINCT m.estudiante_id) AS estudiantes_activos
FROM niveles n
LEFT JOIN cursos c ON c.nivel_id = n.id
LEFT JOIN matriculas m ON m.curso_id = c.id AND m.estado = 'ACTIVA'
GROUP BY n.id, n.codigo, n.nombre, n.orden
ORDER BY n.orden;

-- ---------------------------------------------------------------------
-- 3. Ocupación y cupo disponible por curso
-- ---------------------------------------------------------------------
SELECT
    codigo,
    curso,
    nivel_codigo,
    profesor,
    jornada,
    matriculados,
    cupo_maximo,
    cupo_disponible,
    ROUND(matriculados * 100.0 / NULLIF(cupo_maximo, 0), 1) AS ocupacion_pct,
    estado
FROM vw_cursos_detalle
ORDER BY nivel_codigo, codigo;

-- ---------------------------------------------------------------------
-- 4. Matrículas por periodo y estado
-- ---------------------------------------------------------------------
SELECT
    DATE_FORMAT(fecha_matricula, '%Y-%m') AS periodo,
    COUNT(*) AS total,
    SUM(estado = 'ACTIVA') AS activas,
    SUM(estado = 'RETIRADA') AS retiradas,
    SUM(estado = 'FINALIZADA') AS finalizadas
FROM matriculas
GROUP BY DATE_FORMAT(fecha_matricula, '%Y-%m')
ORDER BY periodo DESC;

-- ---------------------------------------------------------------------
-- 5. Ingresos mensuales (registrados vs. anulados)
-- ---------------------------------------------------------------------
SELECT
    periodo,
    pagos_registrados,
    ingreso_total,
    pagos_anulados,
    total_anulado
FROM vw_ingresos_mensuales
ORDER BY periodo DESC;

-- ---------------------------------------------------------------------
-- 6. Ingresos del mes actual por método de pago
-- ---------------------------------------------------------------------
SELECT
    metodo_pago,
    COUNT(*) AS cantidad_pagos,
    SUM(valor) AS total_recaudado
FROM pagos
WHERE estado = 'REGISTRADO'
  AND fecha_pago >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
  AND fecha_pago < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
GROUP BY metodo_pago
ORDER BY total_recaudado DESC;

-- ---------------------------------------------------------------------
-- 7. Resumen de cartera de matrículas activas
-- ---------------------------------------------------------------------
SELECT
    SUM(valor_total) AS facturado,
    SUM(total_pagado) AS recaudado,
    SUM(saldo) AS pendiente,
    SUM(CASE WHEN cuotas_vencidas > 0 THEN saldo ELSE 0 END) AS vencido,
    ROUND(SUM(total_pagado) * 100.0 / NULLIF(SUM(valor_total), 0), 1) AS recaudo_pct
FROM vw_estado_cuenta
WHERE estado_matricula = 'ACTIVA';

-- ---------------------------------------------------------------------
-- 8. Cartera vencida detallada (días de mora)
-- ---------------------------------------------------------------------
SELECT
    estudiante,
    curso,
    numero AS cuota,
    concepto,
    periodo,
    valor,
    pagado,
    saldo,
    fecha_vencimiento,
    dias_mora
FROM vw_cartera_vencida
ORDER BY dias_mora DESC, saldo DESC;

-- ---------------------------------------------------------------------
-- 9. Estado de cuenta de un estudiante (ejemplo: @estudiante_id)
-- ---------------------------------------------------------------------
SELECT
    matricula_id,
    estudiante,
    curso,
    valor_total,
    total_pagado,
    saldo,
    cuotas_total,
    cuotas_pagadas,
    cuotas_vencidas,
    estado_cartera
FROM vw_estado_cuenta
WHERE estudiante_id = @estudiante_id;

SELECT
    q.numero,
    q.concepto,
    q.periodo,
    q.valor,
    q.fecha_vencimiento,
    q.estado,
    COALESCE(SUM(CASE WHEN p.estado = 'REGISTRADO' THEN p.valor ELSE 0 END), 0) AS pagado,
    q.valor - COALESCE(SUM(CASE WHEN p.estado = 'REGISTRADO' THEN p.valor ELSE 0 END), 0) AS saldo
FROM cuotas q
INNER JOIN matriculas m ON m.id = q.matricula_id
LEFT JOIN pagos p ON p.cuota_id = q.id
WHERE m.estudiante_id = @estudiante_id
GROUP BY q.id, q.numero, q.concepto, q.periodo, q.valor, q.fecha_vencimiento, q.estado
ORDER BY q.numero;

-- ---------------------------------------------------------------------
-- 10. Asistencia consolidada por curso
-- ---------------------------------------------------------------------
SELECT
    c.codigo,
    c.nombre AS curso,
    COUNT(DISTINCT s.id) AS sesiones_dictadas,
    SUM(a.estado = 'PRESENTE') AS presentes,
    SUM(a.estado = 'AUSENTE') AS ausentes,
    SUM(a.estado = 'TARDE') AS tardes,
    SUM(a.estado = 'EXCUSA') AS excusas,
    ROUND(
        SUM(a.estado IN ('PRESENTE','TARDE','EXCUSA')) * 100.0 / NULLIF(COUNT(a.id), 0)
    , 1) AS asistencia_pct
FROM cursos c
INNER JOIN sesiones_clase s ON s.curso_id = c.id AND s.estado = 'DICTADA'
INNER JOIN asistencias a ON a.sesion_id = s.id
GROUP BY c.id, c.codigo, c.nombre
ORDER BY c.codigo;

-- ---------------------------------------------------------------------
-- 11. Estudiantes en riesgo por inasistencia (< 80 %)
-- ---------------------------------------------------------------------
SELECT
    estudiante,
    curso,
    sesiones_totales,
    ausentes,
    porcentaje_asistencia
FROM vw_asistencia_estudiante
WHERE sesiones_totales > 0
  AND porcentaje_asistencia < 80
ORDER BY porcentaje_asistencia ASC;

-- ---------------------------------------------------------------------
-- 12. Progreso promedio por curso
-- ---------------------------------------------------------------------
SELECT
    curso,
    COUNT(*) AS estudiantes,
    ROUND(AVG(promedio_ponderado), 2) AS promedio_curso,
    ROUND(AVG(porcentaje_avance), 1) AS avance_promedio
FROM vw_progreso_estudiante
WHERE promedio_ponderado IS NOT NULL
GROUP BY curso_id, curso
ORDER BY promedio_curso DESC;

-- ---------------------------------------------------------------------
-- 13. Ranking académico (top 10)
-- ---------------------------------------------------------------------
SELECT
    estudiante,
    curso,
    promedio_ponderado,
    porcentaje_avance
FROM vw_progreso_estudiante
WHERE promedio_ponderado IS NOT NULL
ORDER BY promedio_ponderado DESC, porcentaje_avance DESC
LIMIT 10;

-- ---------------------------------------------------------------------
-- 14. Próximas evaluaciones (7 días)
-- ---------------------------------------------------------------------
SELECT
    ev.nombre AS evaluacion,
    c.nombre AS curso,
    ev.tipo,
    ev.porcentaje,
    ev.fecha_entrega,
    DATEDIFF(ev.fecha_entrega, CURDATE()) AS dias_restantes
FROM evaluaciones ev
INNER JOIN cursos c ON c.id = ev.curso_id
WHERE ev.fecha_entrega BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
ORDER BY ev.fecha_entrega ASC;

-- ---------------------------------------------------------------------
-- 15. Evaluaciones vencidas con notas pendientes
-- ---------------------------------------------------------------------
SELECT
    ev.id AS evaluacion_id,
    ev.nombre AS evaluacion,
    c.nombre AS curso,
    ev.fecha_entrega,
    COUNT(m.id) AS matriculados,
    COUNT(cal.id) AS calificados,
    COUNT(m.id) - COUNT(cal.id) AS pendientes
FROM evaluaciones ev
INNER JOIN cursos c ON c.id = ev.curso_id
INNER JOIN matriculas m ON m.curso_id = ev.curso_id AND m.estado = 'ACTIVA'
LEFT JOIN calificaciones cal
    ON cal.evaluacion_id = ev.id AND cal.matricula_id = m.id
WHERE ev.fecha_entrega < NOW()
GROUP BY ev.id, ev.nombre, c.nombre, ev.fecha_entrega
HAVING pendientes > 0
ORDER BY ev.fecha_entrega ASC;

-- ---------------------------------------------------------------------
-- 16. Centro de alertas de un usuario (ejemplo: @usuario_id)
-- ---------------------------------------------------------------------
SELECT
    id,
    tipo,
    titulo,
    mensaje,
    referencia_tipo,
    referencia_id,
    fecha_evento,
    leida,
    created_at
FROM alertas
WHERE usuario_id = @usuario_id
ORDER BY leida ASC, fecha_evento ASC, created_at DESC
LIMIT 50;

-- ---------------------------------------------------------------------
-- 17. Agenda semanal de un profesor (ejemplo: @profesor_id)
-- ---------------------------------------------------------------------
SELECT
    h.dia_semana,
    CASE h.dia_semana
        WHEN 1 THEN 'Lunes' WHEN 2 THEN 'Martes' WHEN 3 THEN 'Miércoles'
        WHEN 4 THEN 'Jueves' WHEN 5 THEN 'Viernes' WHEN 6 THEN 'Sábado'
        ELSE 'Domingo'
    END AS dia,
    TIME_FORMAT(h.hora_inicio, '%H:%i') AS inicio,
    TIME_FORMAT(h.hora_fin, '%H:%i') AS fin,
    c.codigo,
    c.nombre AS curso,
    c.aula,
    n.codigo AS nivel
FROM horarios h
INNER JOIN cursos c ON c.id = h.curso_id
INNER JOIN niveles n ON n.id = c.nivel_id
WHERE c.profesor_id = @profesor_id
  AND c.estado IN ('EN_CURSO', 'PLANIFICADO')
ORDER BY h.dia_semana, h.hora_inicio;

-- ---------------------------------------------------------------------
-- 18. Auditoría filtrada por rango y módulo (ejemplo: @desde, @hasta, @modulo)
-- ---------------------------------------------------------------------
SELECT
    a.created_at,
    COALESCE(CONCAT(u.nombres, ' ', u.apellidos), 'Sistema') AS usuario,
    a.accion,
    a.modulo,
    a.descripcion,
    a.ip
FROM auditoria a
LEFT JOIN usuarios u ON u.id = a.usuario_id
WHERE a.created_at >= @desde
  AND a.created_at < @hasta
  AND a.modulo = @modulo
ORDER BY a.created_at DESC
LIMIT 200;
