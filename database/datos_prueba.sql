-- =====================================================================
-- ANGLISH — Datos de prueba
-- Ejecutar SIEMPRE después de database/schema.sql
-- Fecha de corte de los datos: 08/11/2026
-- Contraseñas:
--   Admin123* / Profe123* / Estudiante123* (bcrypt, costo 10)
-- =====================================================================

USE anglish;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM configuracion;
DELETE FROM auditoria;
DELETE FROM recuperacion_claves;
DELETE FROM alertas;
DELETE FROM entregas_tareas;
DELETE FROM calificaciones;
DELETE FROM evaluaciones;
DELETE FROM asistencias;
DELETE FROM sesiones_clase;
DELETE FROM pagos;
DELETE FROM cuotas;
DELETE FROM matriculas;
DELETE FROM horarios;
DELETE FROM cursos;
DELETE FROM entrevistas;
DELETE FROM niveles;
DELETE FROM profesores;
DELETE FROM estudiantes;
DELETE FROM usuarios;
DELETE FROM rol_permiso;
DELETE FROM permisos;
DELETE FROM roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. Roles, permisos y RBAC
-- ---------------------------------------------------------------------

INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'ADMINISTRADOR', 'Acceso total: usuarios, academia, matrículas, pagos, reportes y auditoría.'),
(2, 'PROFESOR', 'Gestiona asistencia, evaluaciones y notas de los cursos asignados.'),
(3, 'ESTUDIANTE', 'Consulta su progreso, asistencia, estado de cuenta y alertas.');

INSERT INTO permisos (id, clave, descripcion, modulo) VALUES
(1,  'dashboard.ver',           'Ver el tablero según el rol',                    'Dashboard'),
(2,  'usuarios.ver',            'Listar y consultar usuarios',                    'Usuarios'),
(3,  'usuarios.crear',          'Crear usuarios',                                 'Usuarios'),
(4,  'usuarios.editar',         'Editar y cambiar estado de usuarios',            'Usuarios'),
(5,  'roles.ver',               'Consultar roles y permisos',                     'Seguridad'),
(6,  'roles.asignar',           'Asignar rol y permisos a usuarios',              'Seguridad'),
(7,  'academico.ver',           'Consultar niveles, cursos y horarios',           'Académico'),
(8,  'niveles.gestionar',       'Crear y editar niveles A1-C1',                   'Académico'),
(9,  'cursos.gestionar',        'Crear y editar cursos',                          'Académico'),
(10, 'horarios.gestionar',      'Crear y editar horarios',                        'Académico'),
(11, 'profesores.gestionar',    'Crear y editar profesores',                      'Académico'),
(12, 'estudiantes.ver',         'Listar y consultar estudiantes',                 'Estudiantes'),
(13, 'estudiantes.crear',       'Registrar estudiantes',                          'Estudiantes'),
(14, 'estudiantes.editar',      'Editar estudiantes y acudientes',                'Estudiantes'),
(15, 'matriculas.ver',          'Consultar matrículas',                           'Matrículas'),
(16, 'matriculas.crear',        'Matricular estudiantes y generar cuotas',        'Matrículas'),
(17, 'matriculas.retirar',      'Retirar matrículas',                             'Matrículas'),
(18, 'pagos.ver',               'Consultar pagos y estado de cuenta',             'Pagos'),
(19, 'pagos.crear',             'Registrar pagos',                                'Pagos'),
(20, 'pagos.anular',            'Anular pagos',                                   'Pagos'),
(21, 'asistencia.ver',          'Consultar asistencia',                           'Asistencia'),
(22, 'asistencia.registrar',    'Registrar y editar asistencia',                  'Asistencia'),
(23, 'evaluaciones.gestionar',  'Crear y editar evaluaciones',                    'Progreso'),
(24, 'calificaciones.ver',      'Consultar calificaciones',                       'Progreso'),
(25, 'calificaciones.registrar','Registrar y editar calificaciones',              'Progreso'),
(26, 'progreso.ver',            'Consultar progreso académico',                   'Progreso'),
(27, 'alertas.ver',             'Consultar el centro de alertas',                 'Alertas'),
(28, 'reportes.ver',            'Generar reportes y exportar CSV',                'Reportes'),
(29, 'auditoria.ver',           'Consultar la auditoría',                         'Auditoría'),
(30, 'configuracion.gestionar', 'Editar la configuración de la academia',         'Configuración');

-- El administrador recibe todos los permisos.
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- El profesor recibe permisos operativos de sus cursos.
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 2, id FROM permisos
WHERE clave IN (
    'dashboard.ver', 'academico.ver', 'estudiantes.ver',
    'asistencia.ver', 'asistencia.registrar',
    'evaluaciones.gestionar', 'calificaciones.ver', 'calificaciones.registrar',
    'progreso.ver', 'alertas.ver', 'reportes.ver'
);

-- El estudiante solo consulta su información (el controlador limita el alcance).
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT 3, id FROM permisos
WHERE clave IN ('dashboard.ver', 'progreso.ver', 'alertas.ver', 'pagos.ver');

-- ---------------------------------------------------------------------
-- 2. Usuarios, perfiles de profesor y estudiante
-- ---------------------------------------------------------------------

INSERT INTO usuarios
(id, rol_id, nombres, apellidos, documento, email, password_hash, telefono, estado, ultimo_login)
VALUES
(1, 1, 'Angie Valentina', 'Sotelo Ramos', '1000000001', 'admin@anglish.edu.co',
 '$2y$10$5t.EpJWrE.sfSlmaN5W7pOB8HU1pbeqHiAEBF7VNuz5PZEtw5kO5C', '3001234567', 'ACTIVO', '2026-11-08 07:05:00'),
(2, 2, 'Laura', 'Méndez', '1000000002', 'laura.mendez@anglish.edu.co',
 '$2y$10$juErdefj5/JjmmCX.qCSrOgKFfqGkr4cYJXo72kngIkiqga.mLqqG', '3102345678', 'ACTIVO', '2026-11-08 06:50:00'),
(3, 2, 'Carlos', 'Ríos', '1000000003', 'carlos.rios@anglish.edu.co',
 '$2y$10$juErdefj5/JjmmCX.qCSrOgKFfqGkr4cYJXo72kngIkiqga.mLqqG', '3113456789', 'ACTIVO', '2026-11-07 18:10:00'),
(4, 2, 'Andrés', 'Torres', '1000000004', 'andres.torres@anglish.edu.co',
 '$2y$10$juErdefj5/JjmmCX.qCSrOgKFfqGkr4cYJXo72kngIkiqga.mLqqG', '3124567890', 'ACTIVO', '2026-11-07 17:30:00'),
(5, 2, 'María Fernanda', 'López', '1000000005', 'maria.lopez@anglish.edu.co',
 '$2y$10$juErdefj5/JjmmCX.qCSrOgKFfqGkr4cYJXo72kngIkiqga.mLqqG', '3135678901', 'ACTIVO', '2026-11-06 19:05:00'),
(6, 3, 'Sara', 'Gómez', '1000000006', 'sara.gomez@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3206789012', 'ACTIVO', '2026-11-08 08:00:00'),
(7, 3, 'Juan Pablo', 'Pérez', '1000000007', 'juan.perez@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3217890123', 'ACTIVO', '2026-11-07 20:15:00'),
(8, 3, 'Valentina', 'Rodríguez', '1000000008', 'valentina.rodriguez@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3228901234', 'ACTIVO', '2026-11-07 19:40:00'),
(9, 3, 'Camilo', 'Herrera', '1000000009', 'camilo.herrera@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3239012345', 'ACTIVO', '2026-11-06 16:25:00'),
(10, 3, 'Daniela', 'Castro', '1000000010', 'daniela.castro@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3240123456', 'ACTIVO', '2026-11-05 15:00:00'),
(11, 3, 'Mateo', 'Salazar', '1000000011', 'mateo.salazar@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3251234567', 'ACTIVO', '2026-11-05 21:10:00'),
(12, 3, 'Isabella', 'Ramírez', '1000000012', 'isabella.ramirez@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3262345678', 'ACTIVO', '2026-11-04 18:35:00'),
(13, 3, 'Tomás', 'Ortiz', '1000000013', 'tomas.ortiz@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3273456789', 'ACTIVO', '2026-11-04 09:20:00'),
(14, 3, 'Manuela', 'Vargas', '1000000014', 'manuela.vargas@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3284567890', 'ACTIVO', '2026-11-03 17:45:00'),
(15, 3, 'Samuel', 'Jiménez', '1000000015', 'samuel.jimenez@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3295678901', 'ACTIVO', '2026-11-03 20:00:00'),
(16, 3, 'Laura', 'Cárdenas', '1000000016', 'laura.cardenas@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3306789012', 'ACTIVO', '2026-11-02 19:15:00'),
(17, 3, 'Nicolás', 'Peña', '1000000017', 'nicolas.pena@anglish.edu.co',
 '$2y$10$IwVrhvOUt21sDynaaR0nEeKl2E9ivBjZMgW20d/iYZx/qrmDOZKKm', '3317890123', 'ACTIVO', '2026-11-02 18:00:00');

INSERT INTO profesores
(id, usuario_id, especialidad, titulo, fecha_contratacion, estado)
VALUES
(1, 2, 'Inglés A1 y B1', 'Licenciada en Lenguas Extranjeras', '2023-02-01', 'ACTIVO'),
(2, 3, 'Inglés conversacional A1 y A2', 'Licenciado en Inglés', '2022-08-15', 'ACTIVO'),
(3, 4, 'Preparación B2 e IELTS', 'Magíster en Educación', '2024-01-20', 'ACTIVO'),
(4, 5, 'Inglés avanzado C1', 'Licenciada en Filología e Idiomas', '2021-07-01', 'ACTIVO');

INSERT INTO estudiantes
(id, usuario_id, codigo, fecha_nacimiento, acudiente, telefono_acudiente, fecha_ingreso, estado)
VALUES
(1,  6,  'EST-2026-001', '2008-04-12', 'Patricia Gómez',     '3151112233', '2026-09-01', 'ACTIVO'),
(2,  7,  'EST-2026-002', '2007-11-03', 'Ricardo Pérez',      '3152223344', '2026-09-01', 'ACTIVO'),
(3,  8,  'EST-2026-003', '2009-02-20', 'Claudia Rodríguez',  '3153334455', '2026-09-02', 'ACTIVO'),
(4,  9,  'EST-2026-004', '2006-07-15', NULL,                 NULL,         '2026-09-01', 'ACTIVO'),
(5,  10, 'EST-2026-005', '2008-09-01', 'Mónica Castro',      '3154445566', '2026-09-01', 'ACTIVO'),
(6,  11, 'EST-2026-006', '2005-12-10', NULL,                 NULL,         '2026-09-03', 'ACTIVO'),
(7,  12, 'EST-2026-007', '2009-05-25', 'Jorge Ramírez',      '3155556677', '2026-09-03', 'ACTIVO'),
(8,  13, 'EST-2026-008', '2007-03-08', 'Liliana Ortiz',      '3156667788', '2026-09-04', 'ACTIVO'),
(9,  14, 'EST-2026-009', '2006-10-30', NULL,                 NULL,         '2026-09-04', 'ACTIVO'),
(10, 15, 'EST-2026-010', '2008-01-19', 'Álvaro Jiménez',     '3157778899', '2026-09-05', 'ACTIVO'),
(11, 16, 'EST-2026-011', '2007-06-22', 'Sandra Cárdenas',    '3158889900', '2026-09-05', 'ACTIVO'),
(12, 17, 'EST-2026-012', '2009-08-14', 'Felipe Peña',        '3159990011', '2026-09-06', 'ACTIVO');

-- ---------------------------------------------------------------------
-- 3. Oferta académica
-- ---------------------------------------------------------------------

INSERT INTO niveles (id, codigo, nombre, descripcion, orden, horas) VALUES
(1, 'A1', 'Principiante', 'Comprensión y uso de expresiones cotidianas y presentaciones básicas.', 1, 80),
(2, 'A2', 'Elemental', 'Comunicación en situaciones simples y descripción de experiencias.', 2, 80),
(3, 'B1', 'Intermedio', 'Comprensión de textos y conversación sobre temas conocidos.', 3, 100),
(4, 'B2', 'Intermedio alto', 'Fluidez y argumentación sobre temas generales y técnicos.', 4, 100),
(5, 'C1', 'Avanzado', 'Uso flexible y eficaz del idioma en contextos académicos y profesionales.', 5, 120);

INSERT INTO cursos
(id, nivel_id, profesor_id, codigo, nombre, jornada, aula, cupo_maximo, fecha_inicio, fecha_fin, valor_matricula, estado)
VALUES
(1, 1, 1, 'ING-A1-01', 'Inglés A1 — Fundamentos',        'MAÑANA',   'Aula 201', 20, '2026-09-01', '2026-12-15', 1200000.00, 'EN_CURSO'),
(2, 2, 2, 'ING-A2-01', 'Inglés A2 — Comunicación',       'TARDE',    'Aula 202', 18, '2026-09-01', '2026-12-15', 1300000.00, 'EN_CURSO'),
(3, 3, 1, 'ING-B1-01', 'Inglés B1 — Intermedio',         'NOCHE',    'Aula 203', 15, '2026-09-01', '2026-12-15', 1450000.00, 'EN_CURSO'),
(4, 4, 3, 'ING-B2-01', 'Inglés B2 — Intermedio alto',    'SABATINA', 'Aula 204', 12, '2026-09-01', '2026-12-15', 1600000.00, 'EN_CURSO'),
(5, 5, 4, 'ING-C1-01', 'Inglés C1 — Avanzado',           'NOCHE',    'Aula 205', 10, '2026-09-01', '2026-12-15', 1800000.00, 'EN_CURSO'),
(6, 1, 2, 'ING-A1-02', 'Inglés A1 — Intensivo 2027',     'SABATINA', 'Aula 201', 20, '2027-01-20', '2027-05-15', 1250000.00, 'PLANIFICADO');

INSERT INTO horarios (id, curso_id, dia_semana, hora_inicio, hora_fin, aula) VALUES
(1,  1, 1, '07:00:00', '09:00:00', 'Aula 201'),
(2,  1, 3, '07:00:00', '09:00:00', 'Aula 201'),
(3,  2, 2, '14:00:00', '16:00:00', 'Aula 202'),
(4,  2, 4, '14:00:00', '16:00:00', 'Aula 202'),
(5,  3, 1, '18:00:00', '20:00:00', 'Aula 203'),
(6,  3, 3, '18:00:00', '20:00:00', 'Aula 203'),
(7,  4, 6, '08:00:00', '12:00:00', 'Aula 204'),
(8,  5, 2, '18:00:00', '20:00:00', 'Aula 205'),
(9,  5, 4, '18:00:00', '20:00:00', 'Aula 205'),
(10, 6, 6, '14:00:00', '18:00:00', 'Aula 201');

-- ---------------------------------------------------------------------
-- 4. Matrículas y cuotas (4 cuotas mensuales por matrícula)
-- ---------------------------------------------------------------------

INSERT INTO matriculas
(id, estudiante_id, curso_id, fecha_matricula, valor_total, numero_cuotas, estado, observaciones)
VALUES
(1,  1,  1, '2026-09-01', 1200000.00, 4, 'ACTIVA', 'Estudiante con seguimiento académico.'),
(2,  2,  1, '2026-09-01', 1200000.00, 4, 'ACTIVA', NULL),
(3,  3,  1, '2026-09-02', 1200000.00, 4, 'ACTIVA', NULL),
(4,  4,  2, '2026-09-01', 1300000.00, 4, 'ACTIVA', NULL),
(5,  5,  2, '2026-09-01', 1300000.00, 4, 'ACTIVA', NULL),
(6,  6,  3, '2026-09-03', 1450000.00, 4, 'ACTIVA', NULL),
(7,  7,  3, '2026-09-03', 1450000.00, 4, 'ACTIVA', NULL),
(8,  8,  4, '2026-09-04', 1600000.00, 4, 'ACTIVA', NULL),
(9,  9,  4, '2026-09-04', 1600000.00, 4, 'ACTIVA', NULL),
(10, 10, 5, '2026-09-05', 1800000.00, 4, 'ACTIVA', NULL),
(11, 11, 5, '2026-09-05', 1800000.00, 4, 'ACTIVA', NULL),
(12, 12, 1, '2026-09-06', 1200000.00, 4, 'ACTIVA', 'Ingreso tardío al grupo A1.');

-- Genera 4 cuotas por matrícula. La última absorbe el residuo del redondeo.
INSERT INTO cuotas (matricula_id, numero, concepto, periodo, valor, fecha_vencimiento, estado)
SELECT
    m.id,
    n.n,
    CASE WHEN n.n = 1 THEN 'MATRICULA' ELSE 'MENSUALIDAD' END,
    DATE_FORMAT(DATE_ADD('2026-09-01', INTERVAL (n.n - 1) MONTH), '%Y-%m'),
    CASE
        WHEN n.n < 4 THEN FLOOR(m.valor_total / m.numero_cuotas)
        ELSE m.valor_total - (FLOOR(m.valor_total / m.numero_cuotas) * (m.numero_cuotas - 1))
    END,
    DATE_ADD('2026-09-05', INTERVAL (n.n - 1) MONTH),
    'PENDIENTE'
FROM matriculas m
CROSS JOIN (
    SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
) n;

-- ---------------------------------------------------------------------
-- 5. Pagos registrados (hasta la fecha de corte)
-- ---------------------------------------------------------------------

INSERT INTO pagos (cuota_id, valor, fecha_pago, metodo_pago, referencia, estado, registrado_por)
SELECT
    q.id,
    q.valor,
    LEAST(
        TIMESTAMP(DATE_SUB(q.fecha_vencimiento, INTERVAL 2 DAY), '10:30:00'),
        '2026-11-06 18:00:00'
    ),
    CASE (q.id % 5)
        WHEN 0 THEN 'TRANSFERENCIA'
        WHEN 1 THEN 'EFECTIVO'
        WHEN 2 THEN 'NEQUI'
        WHEN 3 THEN 'TARJETA'
        ELSE 'DAVIPLATA'
    END,
    CONCAT('REC-', LPAD(q.id, 6, '0')),
    'REGISTRADO',
    1
FROM cuotas q
INNER JOIN (
    SELECT 1 AS matricula_id, 1 AS paid_until UNION ALL
    SELECT 2, 3 UNION ALL
    SELECT 3, 4 UNION ALL
    SELECT 4, 2 UNION ALL
    SELECT 5, 3 UNION ALL
    SELECT 6, 4 UNION ALL
    SELECT 7, 2 UNION ALL
    SELECT 8, 3 UNION ALL
    SELECT 9, 4 UNION ALL
    SELECT 10, 2 UNION ALL
    SELECT 11, 3 UNION ALL
    SELECT 12, 1
) p ON p.matricula_id = q.matricula_id AND q.numero <= p.paid_until;

-- Pago anulado de ejemplo: no suma a los ingresos y conserva la historia.
INSERT INTO pagos
(cuota_id, valor, fecha_pago, metodo_pago, referencia, estado, motivo_anulacion,
 registrado_por, anulado_por, anulado_en)
SELECT q.id, q.valor, '2026-09-04 09:00:00', 'EFECTIVO', 'REC-ANULADO-001',
       'ANULADO', 'Pago duplicado registrado por error', 1, 1, '2026-09-04 09:30:00'
FROM cuotas q
WHERE q.matricula_id = 3 AND q.numero = 1;

-- Sincroniza el estado de cada cuota con la suma de pagos vigentes.
UPDATE cuotas q
LEFT JOIN (
    SELECT cuota_id, SUM(valor) AS total
    FROM pagos
    WHERE estado = 'REGISTRADO'
    GROUP BY cuota_id
) p ON p.cuota_id = q.id
SET q.estado = CASE WHEN COALESCE(p.total, 0) >= q.valor THEN 'PAGADA' ELSE 'PENDIENTE' END;

-- ---------------------------------------------------------------------
-- 6. Sesiones de clase y asistencia
-- ---------------------------------------------------------------------

INSERT INTO sesiones_clase
(id, curso_id, fecha, hora_inicio, hora_fin, tema, estado, creado_por)
VALUES
(1,  1, '2026-10-05', '07:00:00', '09:00:00', 'Present simple: rutinas',        'DICTADA',    2),
(2,  1, '2026-10-07', '07:00:00', '09:00:00', 'Vocabulary: daily activities',   'DICTADA',    2),
(3,  1, '2026-10-12', '07:00:00', '09:00:00', 'Present continuous',             'DICTADA',    2),
(4,  1, '2026-10-14', '07:00:00', '09:00:00', 'Listening practice',             'DICTADA',    2),
(5,  1, '2026-10-19', '07:00:00', '09:00:00', 'Review units 1-2',               'DICTADA',    2),
(6,  1, '2026-11-11', '07:00:00', '09:00:00', 'Midterm speaking',               'PROGRAMADA', 2),
(7,  3, '2026-10-05', '18:00:00', '20:00:00', 'Past simple vs present perfect', 'DICTADA',    2),
(8,  3, '2026-10-07', '18:00:00', '20:00:00', 'Reading: short stories',        'DICTADA',    2),
(9,  3, '2026-10-12', '18:00:00', '20:00:00', 'Writing workshop',               'DICTADA',    2),
(10, 3, '2026-10-14', '18:00:00', '20:00:00', 'Conditionals',                   'DICTADA',    2),
(11, 3, '2026-10-19', '18:00:00', '20:00:00', 'Listening: interviews',          'DICTADA',    2),
(12, 3, '2026-11-11', '18:00:00', '20:00:00', 'Midterm presentation',           'PROGRAMADA', 2),
(13, 2, '2026-10-06', '14:00:00', '16:00:00', 'Comparatives and superlatives',  'DICTADA',    3),
(14, 2, '2026-10-08', '14:00:00', '16:00:00', 'Future plans',                   'DICTADA',    3),
(15, 2, '2026-10-13', '14:00:00', '16:00:00', 'Modal verbs',                    'DICTADA',    3),
(16, 2, '2026-10-15', '14:00:00', '16:00:00', 'Speaking practice',              'DICTADA',    3),
(17, 2, '2026-10-20', '14:00:00', '16:00:00', 'Review unit 3',                  'DICTADA',    3),
(18, 2, '2026-11-12', '14:00:00', '16:00:00', 'Midterm exam',                   'PROGRAMADA', 3),
(19, 4, '2026-10-03', '08:00:00', '12:00:00', 'Debate: technology',             'DICTADA',    4),
(20, 4, '2026-10-10', '08:00:00', '12:00:00', 'Passive voice',                  'DICTADA',    4),
(21, 4, '2026-10-17', '08:00:00', '12:00:00', 'Reported speech',                'DICTADA',    4),
(22, 4, '2026-10-24', '08:00:00', '12:00:00', 'Writing an essay',               'DICTADA',    4),
(23, 4, '2026-10-31', '08:00:00', '12:00:00', 'Review and feedback',            'DICTADA',    4),
(24, 4, '2026-11-14', '08:00:00', '12:00:00', 'Midterm project',                'PROGRAMADA', 4),
(25, 5, '2026-10-06', '18:00:00', '20:00:00', 'Advanced conditionals',          'DICTADA',    5),
(26, 5, '2026-10-08', '18:00:00', '20:00:00', 'Academic writing',               'DICTADA',    5),
(27, 5, '2026-10-13', '18:00:00', '20:00:00', 'Debate strategies',              'DICTADA',    5),
(28, 5, '2026-10-15', '18:00:00', '20:00:00', 'Listening: lectures',            'DICTADA',    5),
(29, 5, '2026-10-20', '18:00:00', '20:00:00', 'Review C1',                      'DICTADA',    5),
(30, 5, '2026-11-12', '18:00:00', '20:00:00', 'Midterm essay',                  'PROGRAMADA', 5);

-- Asistencia de las 5 sesiones dictadas para cada matrícula activa.
INSERT INTO asistencias
(sesion_id, matricula_id, estado, observacion, registrado_por, created_at)
SELECT
    s.id,
    m.id,
    CASE
        WHEN (s.id * 7 + m.id * 3) % 23 = 0 THEN 'AUSENTE'
        WHEN (s.id * 5 + m.id * 2) % 17 = 0 THEN 'TARDE'
        WHEN (s.id + m.id) % 29 = 0 THEN 'EXCUSA'
        ELSE 'PRESENTE'
    END,
    NULL,
    c.profesor_id + 1,
    TIMESTAMP(s.fecha, s.hora_fin)
FROM sesiones_clase s
INNER JOIN cursos c ON c.id = s.curso_id
INNER JOIN matriculas m ON m.curso_id = s.curso_id AND m.estado = 'ACTIVA'
WHERE s.estado = 'DICTADA';

-- Caso de riesgo: Sara Gómez falta a 2 de 5 sesiones (60 % de asistencia).
UPDATE asistencias
SET estado = 'AUSENTE', observacion = 'No asistió sin excusa'
WHERE matricula_id = 1 AND sesion_id IN (3, 4);

-- ---------------------------------------------------------------------
-- 7. Evaluaciones y calificaciones
-- ---------------------------------------------------------------------

INSERT INTO evaluaciones
(id, curso_id, nombre, tipo, porcentaje, fecha_entrega, descripcion, estado, creado_por)
VALUES
(1,  1, 'Quiz 1 — Present simple',      'QUIZ',          20.00, '2026-09-25 10:00:00', 'Uso de rutinas y vocabulario básico.', 'CERRADA',    2),
(2,  1, 'Taller 1 — Daily routines',    'TALLER',        25.00, '2026-10-16 10:00:00', 'Texto corto sobre la rutina diaria.',   'CERRADA',    2),
(3,  1, 'Parcial — Unidades 1 y 2',     'PARCIAL',       25.00, '2026-11-13 10:00:00', 'Evaluación oral y escrita.',            'PROGRAMADA', 2),
(4,  1, 'Proyecto final — My city',     'PROYECTO',      30.00, '2026-12-04 10:00:00', 'Presentación sobre la ciudad natal.',   'PROGRAMADA', 2),
(5,  2, 'Quiz 1 — Comparatives',        'QUIZ',          20.00, '2026-09-24 14:00:00', 'Comparaciones y superlativos.',         'CERRADA',    3),
(6,  2, 'Taller 1 — Future plans',      'TALLER',        25.00, '2026-10-15 14:00:00', 'Planes a futuro con will y going to.',  'CERRADA',    3),
(7,  2, 'Parcial — Unidad 3',           'PARCIAL',       25.00, '2026-11-12 14:00:00', 'Evaluación de mitad de periodo.',       'PROGRAMADA', 3),
(8,  2, 'Proyecto final — Travel plan', 'PROYECTO',      30.00, '2026-12-03 14:00:00', 'Presentación de un plan de viaje.',     'PROGRAMADA', 3),
(9,  3, 'Quiz 1 — Past tenses',         'QUIZ',          20.00, '2026-09-25 18:00:00', 'Pasado simple y perfecto.',             'CERRADA',    2),
(10, 3, 'Taller 1 — Short story',       'TALLER',        25.00, '2026-10-16 18:00:00', 'Escritura de una historia corta.',      'CERRADA',    2),
(11, 3, 'Parcial — Conditionals',       'PARCIAL',       25.00, '2026-11-13 18:00:00', 'Condicionales y lectura.',              'PROGRAMADA', 2),
(12, 3, 'Proyecto final — Presentation','PROYECTO',      30.00, '2026-12-04 18:00:00', 'Presentación oral intermedia.',         'PROGRAMADA', 2),
(13, 4, 'Quiz 1 — Passive voice',       'QUIZ',          20.00, '2026-09-26 08:00:00', 'Voz pasiva en contextos formales.',     'CERRADA',    4),
(14, 4, 'Taller 1 — Reported speech',   'TALLER',        25.00, '2026-10-17 08:00:00', 'Discurso reportado.',                   'CERRADA',    4),
(15, 4, 'Parcial — Writing',            'PARCIAL',       25.00, '2026-11-14 08:00:00', 'Ensayo argumentativo.',                 'PROGRAMADA', 4),
(16, 4, 'Proyecto final — Debate',      'PROYECTO',      30.00, '2026-12-05 08:00:00', 'Debate académico.',                     'PROGRAMADA', 4),
(17, 5, 'Quiz 1 — Advanced grammar',    'QUIZ',          20.00, '2026-09-24 18:00:00', 'Estructuras avanzadas.',                'CERRADA',    5),
(18, 5, 'Taller 1 — Academic writing',  'TALLER',        25.00, '2026-10-15 18:00:00', 'Ensayo académico.',                     'CERRADA',    5),
(19, 5, 'Parcial — Debate',             'PARCIAL',       25.00, '2026-11-12 18:00:00', 'Argumentación oral.',                   'PROGRAMADA', 5),
(20, 5, 'Proyecto final — Research',    'PROYECTO',      30.00, '2026-12-03 18:00:00', 'Presentación de investigación.',        'PROGRAMADA', 5);

-- Calificaciones de las evaluaciones ya cerradas (2 por estudiante).
INSERT INTO calificaciones
(evaluacion_id, matricula_id, nota, observacion, registrado_por, created_at)
SELECT
    ev.id,
    m.id,
    LEAST(5.0, GREATEST(1.0, 3.0 + ((ev.id * 3 + m.id * 2) % 21) / 10)),
    'Retroalimentación registrada en clase.',
    c.profesor_id + 1,
    TIMESTAMP(ev.fecha_entrega, '12:00:00')
FROM evaluaciones ev
INNER JOIN cursos c ON c.id = ev.curso_id
INNER JOIN matriculas m ON m.curso_id = ev.curso_id AND m.estado = 'ACTIVA'
WHERE ev.fecha_entrega < '2026-11-08 00:00:00';

-- Caso de bajo rendimiento: Sara Gómez (promedio ponderado 2.53).
UPDATE calificaciones SET nota = 2.2, observacion = 'Dificultad con la conjugación.'
WHERE evaluacion_id = 1 AND matricula_id = 1;
UPDATE calificaciones SET nota = 2.8, observacion = 'Debe reforzar vocabulario.'
WHERE evaluacion_id = 2 AND matricula_id = 1;

-- ---------------------------------------------------------------------
-- 8. Alertas (evaluaciones próximas, cartera, rendimiento y asistencia)
-- ---------------------------------------------------------------------

-- Estudiantes: evaluaciones que vencen en los próximos 7 días.
INSERT IGNORE INTO alertas
(usuario_id, tipo, titulo, mensaje, referencia_tipo, referencia_id, fecha_evento, leida, created_at)
SELECT
    u.id,
    'EVALUACION',
    CONCAT('Evaluación próxima: ', ev.nombre),
    CONCAT('Tu ', ev.tipo, ' de ', c.nombre, ' vence el ',
           DATE_FORMAT(ev.fecha_entrega, '%d/%m/%Y %H:%i'), '.'),
    'evaluacion',
    ev.id,
    ev.fecha_entrega,
    0,
    '2026-11-08 07:00:00'
FROM evaluaciones ev
INNER JOIN cursos c ON c.id = ev.curso_id
INNER JOIN matriculas m ON m.curso_id = ev.curso_id AND m.estado = 'ACTIVA'
INNER JOIN estudiantes e ON e.id = m.estudiante_id
INNER JOIN usuarios u ON u.id = e.usuario_id
WHERE ev.fecha_entrega BETWEEN '2026-11-08 00:00:00' AND '2026-11-15 23:59:59';

-- Profesores: recordatorio de la evaluación próxima de sus cursos.
INSERT IGNORE INTO alertas
(usuario_id, tipo, titulo, mensaje, referencia_tipo, referencia_id, fecha_evento, leida, created_at)
SELECT
    u.id,
    'EVALUACION',
    CONCAT('Evaluación próxima en ', c.nombre),
    CONCAT(ev.nombre, ' vence el ', DATE_FORMAT(ev.fecha_entrega, '%d/%m/%Y %H:%i'),
           '. Revisa el plan evaluativo.'),
    'evaluacion',
    ev.id,
    ev.fecha_entrega,
    0,
    '2026-11-08 07:00:00'
FROM evaluaciones ev
INNER JOIN cursos c ON c.id = ev.curso_id
INNER JOIN profesores p ON p.id = c.profesor_id
INNER JOIN usuarios u ON u.id = p.usuario_id
WHERE ev.fecha_entrega BETWEEN '2026-11-08 00:00:00' AND '2026-11-15 23:59:59';

-- Estudiantes: cuotas vencidas con saldo.
INSERT IGNORE INTO alertas
(usuario_id, tipo, titulo, mensaje, referencia_tipo, referencia_id, fecha_evento, leida, created_at)
SELECT
    e.usuario_id,
    'PAGO',
    CONCAT('Cuota vencida #', v.numero),
    CONCAT('La cuota de ', v.curso, ' venció el ', DATE_FORMAT(v.fecha_vencimiento, '%d/%m/%Y'),
           ' y tiene un saldo de $', FORMAT(v.saldo, 0), ' COP.'),
    'cuota',
    v.cuota_id,
    TIMESTAMP(v.fecha_vencimiento, '23:59:59'),
    0,
    '2026-11-08 07:00:00'
FROM vw_cartera_vencida v
INNER JOIN estudiantes e ON e.id = v.estudiante_id;

-- Administrador: misma cartera vencida, para seguimiento.
INSERT IGNORE INTO alertas
(usuario_id, tipo, titulo, mensaje, referencia_tipo, referencia_id, fecha_evento, leida, created_at)
SELECT
    1,
    'PAGO',
    CONCAT('Cartera vencida: ', v.estudiante),
    CONCAT('Cuota #', v.numero, ' de ', v.curso, ' con saldo de $',
           FORMAT(v.saldo, 0), ' COP y ', v.dias_mora, ' días de mora.'),
    'cuota',
    v.cuota_id,
    TIMESTAMP(v.fecha_vencimiento, '23:59:59'),
    0,
    '2026-11-08 07:00:00'
FROM vw_cartera_vencida v;

-- Estudiantes: bajo rendimiento (promedio < 3.0).
INSERT IGNORE INTO alertas
(usuario_id, tipo, titulo, mensaje, referencia_tipo, referencia_id, fecha_evento, leida, created_at)
SELECT
    e.usuario_id,
    'RENDIMIENTO',
    'Bajo rendimiento académico',
    CONCAT('Tu promedio ponderado en ', p.curso, ' es ',
           FORMAT(p.promedio_ponderado, 2), '. El mínimo aprobatorio es 3.0.'),
    'matricula',
    p.matricula_id,
    '2026-11-08 07:00:00',
    0,
    '2026-11-08 07:00:00'
FROM vw_progreso_estudiante p
INNER JOIN estudiantes e ON e.id = p.estudiante_id
WHERE p.promedio_ponderado IS NOT NULL
  AND p.promedio_ponderado < 3.0;

-- Estudiantes: asistencia menor al 80 %.
INSERT IGNORE INTO alertas
(usuario_id, tipo, titulo, mensaje, referencia_tipo, referencia_id, fecha_evento, leida, created_at)
SELECT
    e.usuario_id,
    'ASISTENCIA',
    'Asistencia en riesgo',
    CONCAT('Tu asistencia en ', a.curso, ' es ', FORMAT(a.porcentaje_asistencia, 1),
           ' %. El mínimo requerido es 80 %.'),
    'matricula',
    a.matricula_id,
    '2026-11-08 07:00:00',
    0,
    '2026-11-08 07:00:00'
FROM vw_asistencia_estudiante a
INNER JOIN estudiantes e ON e.id = a.estudiante_id
WHERE a.sesiones_totales > 0
  AND a.porcentaje_asistencia < 80;

-- ---------------------------------------------------------------------
-- 9. Auditoría y configuración
-- ---------------------------------------------------------------------

INSERT INTO auditoria
(usuario_id, accion, modulo, descripcion, ip, user_agent, created_at)
VALUES
(1, 'LOGIN',   'Autenticación', 'Inicio de sesión del administrador.',                 '127.0.0.1', 'Seed Anglish', '2026-11-08 07:05:00'),
(2, 'LOGIN',   'Autenticación', 'Inicio de sesión de la profesora Laura Méndez.',      '127.0.0.1', 'Seed Anglish', '2026-11-08 06:50:00'),
(3, 'LOGIN',   'Autenticación', 'Inicio de sesión del profesor Carlos Ríos.',          '127.0.0.1', 'Seed Anglish', '2026-11-07 18:10:00'),
(6, 'LOGIN',   'Autenticación', 'Inicio de sesión de la estudiante Sara Gómez.',       '127.0.0.1', 'Seed Anglish', '2026-11-08 08:00:00'),
(1, 'CREAR',   'Matrículas',   'Matrícula 12 creada para Nicolás Peña en ING-A1-01.', '127.0.0.1', 'Seed Anglish', '2026-09-06 09:15:00'),
(1, 'CREAR',   'Pagos',        'Pago REC-000001 registrado por $300.000 COP.',         '127.0.0.1', 'Seed Anglish', '2026-09-03 10:30:00'),
(2, 'CREAR',   'Asistencia',   'Asistencia de la sesión 5 del curso ING-A1-01.',       '127.0.0.1', 'Seed Anglish', '2026-10-19 09:05:00'),
(2, 'CREAR',   'Calificaciones','Nota 4.5 registrada en el Quiz 1 de ING-A1-01.',     '127.0.0.1', 'Seed Anglish', '2026-09-25 12:10:00'),
(1, 'ANULAR',  'Pagos',        'Pago REC-ANULADO-001 anulado por duplicidad.',         '127.0.0.1', 'Seed Anglish', '2026-09-04 09:30:00'),
(1, 'CONSULTAR','Reportes',    'Consulta de ingresos del periodo 2026-10.',            '127.0.0.1', 'Seed Anglish', '2026-11-07 16:45:00');

INSERT INTO configuracion (clave, valor, tipo, descripcion) VALUES
('academia.nombre',              'Anglish — Academia de inglés', 'TEXTO',   'Nombre comercial.'),
('academia.nit',                 '900.123.456-7',                'TEXTO',   'NIT de la academia.'),
('academia.direccion',           'Calle 45 # 12-34, Bogotá',     'TEXTO',   'Dirección principal.'),
('academia.telefono',            '+57 601 555 0123',             'TEXTO',   'Teléfono de contacto.'),
('academia.email',               'contacto@anglish.edu.co',      'TEXTO',   'Correo institucional.'),
('academia.sitio_web',           'https://www.anglish.edu.co',   'TEXTO',   'Sitio web.'),
('moneda',                       'COP',                          'TEXTO',   'Moneda del sistema.'),
('umbral.asistencia',            '80',                           'NUMERO',  'Porcentaje mínimo de asistencia.'),
('umbral.nota',                  '3.0',                          'NUMERO',  'Nota mínima aprobatoria.'),
('pagos.dia_vencimiento',        '5',                            'NUMERO',  'Día de vencimiento mensual.'),
('alertas.dias_anticipacion',    '7',                            'NUMERO',  'Días de anticipación de alertas.');

-- ---------------------------------------------------------------------
-- 10. Entrevistas de nivelación y Entregas de tareas
-- ---------------------------------------------------------------------

INSERT INTO entrevistas
(id, candidato_nombre, candidato_email, candidato_telefono, nivel_interes_id, fecha_hora, modalidad, enlace_o_lugar, estado, entrevistador_id, resultado_nivel, observaciones)
VALUES
(1, 'Andrea Rincón Méndez', 'andrea.rincon@gmail.com', '3157891234', 2, '2026-11-15 10:00:00', 'VIRTUAL', 'meet.google.com/ang-level-101', 'PROGRAMADA', 2, NULL, 'Interesada en programa intensivo de A2 para intercambio.'),
(2, 'Felipe Castro Guzmán', 'felipe.castro@hotmail.com', '3189998877', 3, '2026-11-16 15:30:00', 'PRESENCIAL', 'Sede Principal - Salón 201', 'PROGRAMADA', 3, NULL, 'Tiene conocimientos previos de inglés en bachillerato.'),
(3, 'Mariana Silva Torres', 'mariana.silva@yahoo.com', '3201122334', 1, '2026-11-08 11:00:00', 'VIRTUAL', 'meet.google.com/ang-level-102', 'REALIZADA', 2, 'A1', 'Excelente actitud, se recomienda iniciar en A1 Fundamentos.'),
(4, 'David Morales Cruz', 'david.morales@outlook.com', '3114455667', 4, '2026-11-07 16:00:00', 'VIRTUAL', 'meet.google.com/ang-level-103', 'APROBADA', 2, 'B1', 'Nivel B1 convalidado, apto para matricular en B2 Intermedio Alto.');

INSERT INTO entregas_tareas
(id, evaluacion_id, matricula_id, archivo_url, archivo_nombre, comentario_estudiante, fecha_entrega, estado, retroalimentacion, calificacion_id)
VALUES
(1, 1, 1, 'uploads/tareas/taller1_sara_gomez.pdf', 'taller1_sara_gomez.pdf', 'Adjunto la solución al Taller de Vocabulario y Verb to be con todos los ejercicios completos.', '2026-09-21 16:30:00', 'CALIFICADA', 'Excelente desarrollo y correcta conjugación de verbos regulares.', NULL),
(2, 3, 1, 'uploads/tareas/parcial_sara_gomez.pdf', 'parcial_sara_gomez.pdf', 'Envío el desarrollo del Parcial Escrito Unidad 1.', '2026-10-18 11:20:00', 'CALIFICADA', 'Muy buen puntaje, repasar uso de artículos definidos.', NULL),
(3, 4, 1, 'uploads/tareas/speaking_sara_gomez.mp4', 'speaking_sara_gomez.mp4', 'Envío el video de la presentación oral sobre mi rutina diaria.', '2026-11-08 14:15:00', 'ENTREGADA', NULL, NULL);

-- ---------------------------------------------------------------------
-- Fin de los datos de prueba
-- ---------------------------------------------------------------------
