-- =====================================================================
-- ANGLISH — Academia de inglés
-- Esquema de base de datos (MySQL 8.0 / MariaDB 10.4+)
-- Autora: Angie Valentina Sotelo Ramos — ADSO SENA
-- ---------------------------------------------------------------------
-- Ejecutar con: mysql -u root < database/schema.sql
-- ADVERTENCIA: elimina y recrea las tablas de la base de datos `anglish`.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS anglish
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE anglish;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS vw_cartera_vencida;
DROP VIEW IF EXISTS vw_ingresos_mensuales;
DROP VIEW IF EXISTS vw_progreso_estudiante;
DROP VIEW IF EXISTS vw_asistencia_estudiante;
DROP VIEW IF EXISTS vw_estado_cuenta;
DROP VIEW IF EXISTS vw_cursos_detalle;
DROP VIEW IF EXISTS vw_estudiantes;

DROP TABLE IF EXISTS configuracion;
DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS recuperacion_claves;
DROP TABLE IF EXISTS alertas;
DROP TABLE IF EXISTS entregas_tareas;
DROP TABLE IF EXISTS calificaciones;
DROP TABLE IF EXISTS evaluaciones;
DROP TABLE IF EXISTS asistencias;
DROP TABLE IF EXISTS sesiones_clase;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS cuotas;
DROP TABLE IF EXISTS matriculas;
DROP TABLE IF EXISTS horarios;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS entrevistas;
DROP TABLE IF EXISTS niveles;
DROP TABLE IF EXISTS profesores;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS rol_permiso;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. Seguridad y RBAC
-- ---------------------------------------------------------------------

CREATE TABLE roles (
    id            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(30)  NOT NULL,
    descripcion   VARCHAR(150) NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permisos (
    id            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    clave         VARCHAR(60)  NOT NULL,
    descripcion   VARCHAR(150) NOT NULL,
    modulo        VARCHAR(40)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_permisos_clave (clave),
    KEY idx_permisos_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rol_permiso (
    rol_id        TINYINT UNSIGNED  NOT NULL,
    permiso_id    SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rolpermiso_rol
        FOREIGN KEY (rol_id) REFERENCES roles (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rolpermiso_permiso
        FOREIGN KEY (permiso_id) REFERENCES permisos (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rol_id             TINYINT UNSIGNED NOT NULL,
    nombres            VARCHAR(80)  NOT NULL,
    apellidos          VARCHAR(80)  NOT NULL,
    documento          VARCHAR(20)  NOT NULL,
    email              VARCHAR(120) NOT NULL,
    password_hash      VARCHAR(255) NOT NULL,
    telefono           VARCHAR(20)  NULL,
    estado             ENUM('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    intentos_fallidos  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta    DATETIME NULL,
    ultimo_login       DATETIME NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_documento (documento),
    UNIQUE KEY uk_usuarios_email (email),
    KEY idx_usuarios_rol_estado (rol_id, estado),
    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (rol_id) REFERENCES roles (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_usuarios_intentos CHECK (intentos_fallidos <= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estudiantes (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id         INT UNSIGNED NOT NULL,
    codigo             VARCHAR(20)  NOT NULL,
    fecha_nacimiento   DATE NOT NULL,
    acudiente          VARCHAR(120) NULL,
    telefono_acudiente VARCHAR(20)  NULL,
    fecha_ingreso      DATE NOT NULL,
    estado             ENUM('ACTIVO','RETIRADO','EGRESADO') NOT NULL DEFAULT 'ACTIVO',
    observaciones      TEXT NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_estudiantes_usuario (usuario_id),
    UNIQUE KEY uk_estudiantes_codigo (codigo),
    KEY idx_estudiantes_estado (estado),
    CONSTRAINT fk_estudiantes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profesores (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id         INT UNSIGNED NOT NULL,
    especialidad       VARCHAR(100) NOT NULL,
    titulo             VARCHAR(120) NULL,
    fecha_contratacion DATE NOT NULL,
    estado             ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_profesores_usuario (usuario_id),
    KEY idx_profesores_estado (estado),
    CONSTRAINT fk_profesores_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Gestión académica
-- ---------------------------------------------------------------------

CREATE TABLE niveles (
    id           TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo       VARCHAR(2)  NOT NULL,
    nombre       VARCHAR(50) NOT NULL,
    descripcion  VARCHAR(255) NULL,
    orden        TINYINT UNSIGNED NOT NULL,
    horas        SMALLINT UNSIGNED NOT NULL DEFAULT 80,
    PRIMARY KEY (id),
    UNIQUE KEY uk_niveles_codigo (codigo),
    UNIQUE KEY uk_niveles_orden (orden),
    CONSTRAINT chk_niveles_horas CHECK (horas > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cursos (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nivel_id         TINYINT UNSIGNED NOT NULL,
    profesor_id      INT UNSIGNED NOT NULL,
    codigo           VARCHAR(20)  NOT NULL,
    nombre           VARCHAR(100) NOT NULL,
    jornada          ENUM('MAÑANA','TARDE','NOCHE','SABATINA') NOT NULL,
    aula             VARCHAR(30) NULL,
    cupo_maximo      SMALLINT UNSIGNED NOT NULL,
    fecha_inicio     DATE NOT NULL,
    fecha_fin        DATE NOT NULL,
    valor_matricula  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado           ENUM('PLANIFICADO','EN_CURSO','FINALIZADO','CANCELADO') NOT NULL DEFAULT 'PLANIFICADO',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_cursos_codigo (codigo),
    KEY idx_cursos_nivel_estado (nivel_id, estado),
    KEY idx_cursos_profesor (profesor_id),
    CONSTRAINT fk_cursos_nivel
        FOREIGN KEY (nivel_id) REFERENCES niveles (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cursos_profesor
        FOREIGN KEY (profesor_id) REFERENCES profesores (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_cursos_cupo CHECK (cupo_maximo > 0),
    CONSTRAINT chk_cursos_fechas CHECK (fecha_fin > fecha_inicio),
    CONSTRAINT chk_cursos_valor CHECK (valor_matricula >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE horarios (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id     INT UNSIGNED NOT NULL,
    dia_semana   TINYINT UNSIGNED NOT NULL COMMENT '1=Lunes ... 7=Domingo',
    hora_inicio  TIME NOT NULL,
    hora_fin     TIME NOT NULL,
    aula         VARCHAR(30) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_horarios_curso_dia_hora (curso_id, dia_semana, hora_inicio),
    KEY idx_horarios_curso_dia (curso_id, dia_semana),
    CONSTRAINT fk_horarios_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_horarios_dia CHECK (dia_semana BETWEEN 1 AND 7),
    CONSTRAINT chk_horarios_horas CHECK (hora_fin > hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sesiones_clase (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id     INT UNSIGNED NOT NULL,
    fecha        DATE NOT NULL,
    hora_inicio  TIME NOT NULL,
    hora_fin     TIME NOT NULL,
    tema         VARCHAR(150) NOT NULL,
    observacion  VARCHAR(255) NULL,
    estado       ENUM('PROGRAMADA','DICTADA','CANCELADA') NOT NULL DEFAULT 'PROGRAMADA',
    creado_por   INT UNSIGNED NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_sesiones_curso_fecha_hora (curso_id, fecha, hora_inicio),
    KEY idx_sesiones_curso_fecha (curso_id, fecha),
    CONSTRAINT fk_sesiones_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sesiones_usuario
        FOREIGN KEY (creado_por) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_sesiones_horas CHECK (hora_fin > hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Matrículas, cuotas y pagos
-- ---------------------------------------------------------------------

CREATE TABLE matriculas (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    estudiante_id   INT UNSIGNED NOT NULL,
    curso_id        INT UNSIGNED NOT NULL,
    fecha_matricula DATE NOT NULL,
    valor_total     DECIMAL(12,2) NOT NULL,
    numero_cuotas   TINYINT UNSIGNED NOT NULL DEFAULT 1,
    estado          ENUM('ACTIVA','RETIRADA','FINALIZADA') NOT NULL DEFAULT 'ACTIVA',
    motivo_retiro   VARCHAR(255) NULL,
    retirada_por    INT UNSIGNED NULL,
    retirada_en     DATETIME NULL,
    observaciones   TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_matriculas_estudiante_curso (estudiante_id, curso_id),
    KEY idx_matriculas_curso_estado (curso_id, estado),
    KEY idx_matriculas_estudiante_estado (estudiante_id, estado),
    CONSTRAINT fk_matriculas_estudiante
        FOREIGN KEY (estudiante_id) REFERENCES estudiantes (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_matriculas_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_matriculas_retirada_por
        FOREIGN KEY (retirada_por) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_matriculas_valor CHECK (valor_total > 0),
    CONSTRAINT chk_matriculas_cuotas CHECK (numero_cuotas BETWEEN 1 AND 24)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cuotas (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    matricula_id      INT UNSIGNED NOT NULL,
    numero            TINYINT UNSIGNED NOT NULL,
    concepto          ENUM('MATRICULA','MENSUALIDAD','CERTIFICADO','OTRO') NOT NULL DEFAULT 'MENSUALIDAD',
    periodo           CHAR(7) NOT NULL COMMENT 'Formato YYYY-MM',
    valor             DECIMAL(12,2) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado            ENUM('PENDIENTE','PAGADA','ANULADA') NOT NULL DEFAULT 'PENDIENTE',
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_cuotas_matricula_numero (matricula_id, numero),
    KEY idx_cuotas_vencimiento_estado (fecha_vencimiento, estado),
    KEY idx_cuotas_matricula_estado (matricula_id, estado),
    CONSTRAINT fk_cuotas_matricula
        FOREIGN KEY (matricula_id) REFERENCES matriculas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_cuotas_valor CHECK (valor > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pagos (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cuota_id          INT UNSIGNED NOT NULL,
    valor             DECIMAL(12,2) NOT NULL,
    fecha_pago        DATETIME NOT NULL,
    metodo_pago       ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','NEQUI','DAVIPLATA') NOT NULL,
    referencia        VARCHAR(60) NULL,
    estado            ENUM('REGISTRADO','ANULADO') NOT NULL DEFAULT 'REGISTRADO',
    motivo_anulacion  VARCHAR(255) NULL,
    registrado_por    INT UNSIGNED NOT NULL,
    anulado_por       INT UNSIGNED NULL,
    anulado_en        DATETIME NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pagos_fecha_estado (fecha_pago, estado),
    KEY idx_pagos_cuota_estado (cuota_id, estado),
    CONSTRAINT fk_pagos_cuota
        FOREIGN KEY (cuota_id) REFERENCES cuotas (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pagos_registrado_por
        FOREIGN KEY (registrado_por) REFERENCES usuarios (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pagos_anulado_por
        FOREIGN KEY (anulado_por) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_pagos_valor CHECK (valor > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Asistencia, evaluación y progreso
-- ---------------------------------------------------------------------

CREATE TABLE asistencias (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sesion_id      INT UNSIGNED NOT NULL,
    matricula_id   INT UNSIGNED NOT NULL,
    estado         ENUM('PRESENTE','AUSENTE','TARDE','EXCUSA') NOT NULL DEFAULT 'PRESENTE',
    observacion    VARCHAR(200) NULL,
    registrado_por INT UNSIGNED NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_asistencias_sesion_matricula (sesion_id, matricula_id),
    KEY idx_asistencias_matricula (matricula_id),
    KEY idx_asistencias_estado (estado),
    CONSTRAINT fk_asistencias_sesion
        FOREIGN KEY (sesion_id) REFERENCES sesiones_clase (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_asistencias_matricula
        FOREIGN KEY (matricula_id) REFERENCES matriculas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_asistencias_usuario
        FOREIGN KEY (registrado_por) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE evaluaciones (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id      INT UNSIGNED NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    tipo          ENUM('TALLER','QUIZ','PARCIAL','PROYECTO','EXAMEN_FINAL') NOT NULL,
    porcentaje    DECIMAL(5,2) NOT NULL,
    fecha_entrega DATETIME NOT NULL,
    descripcion   TEXT NULL,
    estado        ENUM('PROGRAMADA','ABIERTA','CERRADA') NOT NULL DEFAULT 'PROGRAMADA',
    creado_por    INT UNSIGNED NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_evaluaciones_curso_nombre (curso_id, nombre),
    KEY idx_evaluaciones_curso_fecha (curso_id, fecha_entrega),
    CONSTRAINT fk_evaluaciones_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_evaluaciones_usuario
        FOREIGN KEY (creado_por) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_evaluaciones_porcentaje CHECK (porcentaje > 0 AND porcentaje <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE calificaciones (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluacion_id  INT UNSIGNED NOT NULL,
    matricula_id   INT UNSIGNED NOT NULL,
    nota           DECIMAL(2,1) NOT NULL,
    observacion    VARCHAR(200) NULL,
    registrado_por INT UNSIGNED NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_calificaciones_evaluacion_matricula (evaluacion_id, matricula_id),
    KEY idx_calificaciones_matricula (matricula_id),
    CONSTRAINT fk_calificaciones_evaluacion
        FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_calificaciones_matricula
        FOREIGN KEY (matricula_id) REFERENCES matriculas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_calificaciones_usuario
        FOREIGN KEY (registrado_por) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_calificaciones_nota CHECK (nota >= 1.0 AND nota <= 5.0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entregas_tareas (
    id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluacion_id           INT UNSIGNED NOT NULL,
    matricula_id            INT UNSIGNED NOT NULL,
    archivo_url             VARCHAR(255) NULL,
    archivo_nombre          VARCHAR(150) NULL,
    comentario_estudiante   TEXT NULL,
    fecha_entrega           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado                  ENUM('ENTREGADA','CALIFICADA','DEVUELTA') NOT NULL DEFAULT 'ENTREGADA',
    retroalimentacion       TEXT NULL,
    calificacion_id         BIGINT UNSIGNED NULL,
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_entrega_evaluacion_matricula (evaluacion_id, matricula_id),
    KEY idx_entregas_evaluacion (evaluacion_id),
    KEY idx_entregas_matricula (matricula_id),
    CONSTRAINT fk_entregas_evaluacion
        FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_entregas_matricula
        FOREIGN KEY (matricula_id) REFERENCES matriculas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_entregas_calificacion
        FOREIGN KEY (calificacion_id) REFERENCES calificaciones (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entrevistas (
    id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    candidato_nombre        VARCHAR(120) NOT NULL,
    candidato_email         VARCHAR(120) NOT NULL,
    candidato_telefono      VARCHAR(25) NOT NULL,
    nivel_interes_id        TINYINT UNSIGNED NULL,
    fecha_hora              DATETIME NOT NULL,
    modalidad               ENUM('VIRTUAL','PRESENCIAL') NOT NULL DEFAULT 'VIRTUAL',
    enlace_o_lugar          VARCHAR(255) NULL,
    estado                  ENUM('PROGRAMADA','REALIZADA','CANCELADA','APROBADA','NO_ASISTIO') NOT NULL DEFAULT 'PROGRAMADA',
    entrevistador_id        INT UNSIGNED NULL,
    resultado_nivel         VARCHAR(10) NULL,
    observaciones           TEXT NULL,
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_entrevistas_fecha (fecha_hora),
    KEY idx_entrevistas_estado (estado),
    CONSTRAINT fk_entrevistas_nivel
        FOREIGN KEY (nivel_interes_id) REFERENCES niveles (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_entrevistas_entrevistador
        FOREIGN KEY (entrevistador_id) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Alertas, recuperación de clave, auditoría y configuración
-- ---------------------------------------------------------------------

CREATE TABLE alertas (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id      INT UNSIGNED NOT NULL,
    tipo            ENUM('EVALUACION','PAGO','ASISTENCIA','RENDIMIENTO') NOT NULL,
    titulo          VARCHAR(120) NOT NULL,
    mensaje         VARCHAR(255) NOT NULL,
    referencia_tipo ENUM('evaluacion','cuota','matricula') NOT NULL,
    referencia_id   INT UNSIGNED NOT NULL,
    fecha_evento    DATETIME NULL,
    leida           TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_alertas_dedupe (usuario_id, tipo, referencia_tipo, referencia_id),
    KEY idx_alertas_usuario_leida (usuario_id, leida),
    KEY idx_alertas_tipo (tipo),
    CONSTRAINT fk_alertas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recuperacion_claves (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expira_en   DATETIME NOT NULL,
    usado_en    DATETIME NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_recuperacion_token (token_hash),
    KEY idx_recuperacion_usuario (usuario_id),
    CONSTRAINT fk_recuperacion_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auditoria (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NULL,
    accion      VARCHAR(30) NOT NULL,
    modulo      VARCHAR(40) NOT NULL,
    descripcion VARCHAR(255) NULL,
    ip          VARCHAR(45) NULL,
    user_agent  VARCHAR(255) NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditoria_fecha_usuario (created_at, usuario_id),
    KEY idx_auditoria_modulo_accion (modulo, accion),
    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE configuracion (
    clave       VARCHAR(50) NOT NULL,
    valor       VARCHAR(255) NOT NULL,
    tipo        ENUM('TEXTO','NUMERO','BOOLEAN','JSON') NOT NULL DEFAULT 'TEXTO',
    descripcion VARCHAR(150) NULL,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Vistas de cálculo (no almacenan datos duplicados)
-- ---------------------------------------------------------------------

CREATE OR REPLACE VIEW vw_estudiantes AS
SELECT
    e.id,
    e.codigo,
    e.usuario_id,
    u.documento,
    CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo,
    u.email,
    u.telefono,
    e.fecha_nacimiento,
    e.acudiente,
    e.telefono_acudiente,
    e.fecha_ingreso,
    e.estado
FROM estudiantes e
INNER JOIN usuarios u ON u.id = e.usuario_id;

CREATE OR REPLACE VIEW vw_cursos_detalle AS
SELECT
    c.id,
    c.codigo,
    c.nombre AS curso,
    c.nivel_id,
    n.codigo AS nivel_codigo,
    n.nombre AS nivel_nombre,
    c.profesor_id,
    CONCAT(u.nombres, ' ', u.apellidos) AS profesor,
    c.jornada,
    c.aula,
    c.cupo_maximo,
    c.fecha_inicio,
    c.fecha_fin,
    c.valor_matricula,
    c.estado,
    (SELECT COUNT(*) FROM matriculas m
      WHERE m.curso_id = c.id AND m.estado = 'ACTIVA') AS matriculados,
    (c.cupo_maximo - (SELECT COUNT(*) FROM matriculas m
      WHERE m.curso_id = c.id AND m.estado = 'ACTIVA')) AS cupo_disponible
FROM cursos c
INNER JOIN niveles n ON n.id = c.nivel_id
INNER JOIN profesores p ON p.id = c.profesor_id
INNER JOIN usuarios u ON u.id = p.usuario_id;

CREATE OR REPLACE VIEW vw_estado_cuenta AS
SELECT
    m.id AS matricula_id,
    e.id AS estudiante_id,
    CONCAT(u.nombres, ' ', u.apellidos) AS estudiante,
    u.email,
    m.curso_id,
    c.nombre AS curso,
    m.valor_total,
    COALESCE(pg.total_pagado, 0) AS total_pagado,
    ROUND(m.valor_total - COALESCE(pg.total_pagado, 0), 2) AS saldo,
    (SELECT COUNT(*) FROM cuotas q WHERE q.matricula_id = m.id) AS cuotas_total,
    (SELECT COUNT(*) FROM cuotas q
      WHERE q.matricula_id = m.id AND q.estado = 'PAGADA') AS cuotas_pagadas,
    (SELECT COUNT(*) FROM cuotas q
      WHERE q.matricula_id = m.id
        AND q.estado NOT IN ('PAGADA','ANULADA')
        AND q.fecha_vencimiento < CURDATE()) AS cuotas_vencidas,
    m.estado AS estado_matricula,
    CASE
        WHEN m.valor_total - COALESCE(pg.total_pagado, 0) <= 0 THEN 'AL DÍA'
        WHEN EXISTS (
            SELECT 1 FROM cuotas q2
            WHERE q2.matricula_id = m.id
              AND q2.estado NOT IN ('PAGADA','ANULADA')
              AND q2.fecha_vencimiento < CURDATE()
        ) THEN 'EN MORA'
        ELSE 'PENDIENTE'
    END AS estado_cartera
FROM matriculas m
INNER JOIN estudiantes e ON e.id = m.estudiante_id
INNER JOIN usuarios u ON u.id = e.usuario_id
INNER JOIN cursos c ON c.id = m.curso_id
LEFT JOIN (
    SELECT q.matricula_id, SUM(p.valor) AS total_pagado
    FROM pagos p
    INNER JOIN cuotas q ON q.id = p.cuota_id
    WHERE p.estado = 'REGISTRADO'
    GROUP BY q.matricula_id
) pg ON pg.matricula_id = m.id;

CREATE OR REPLACE VIEW vw_asistencia_estudiante AS
SELECT
    m.id AS matricula_id,
    e.id AS estudiante_id,
    CONCAT(u.nombres, ' ', u.apellidos) AS estudiante,
    m.curso_id,
    c.nombre AS curso,
    COUNT(DISTINCT s.id) AS sesiones_totales,
    COALESCE(SUM(CASE WHEN a.estado = 'PRESENTE' THEN 1 ELSE 0 END), 0) AS presentes,
    COALESCE(SUM(CASE WHEN a.estado = 'AUSENTE'  THEN 1 ELSE 0 END), 0) AS ausentes,
    COALESCE(SUM(CASE WHEN a.estado = 'TARDE'    THEN 1 ELSE 0 END), 0) AS tardes,
    COALESCE(SUM(CASE WHEN a.estado = 'EXCUSA'   THEN 1 ELSE 0 END), 0) AS excusas,
    COALESCE(ROUND(
        SUM(CASE WHEN a.estado IN ('PRESENTE','TARDE','EXCUSA') THEN 1 ELSE 0 END)
        * 100.0 / NULLIF(COUNT(DISTINCT s.id), 0)
    , 1), 0) AS porcentaje_asistencia
FROM matriculas m
INNER JOIN estudiantes e ON e.id = m.estudiante_id
INNER JOIN usuarios u ON u.id = e.usuario_id
INNER JOIN cursos c ON c.id = m.curso_id
LEFT JOIN sesiones_clase s
    ON s.curso_id = m.curso_id AND s.estado = 'DICTADA'
LEFT JOIN asistencias a
    ON a.sesion_id = s.id AND a.matricula_id = m.id
GROUP BY m.id, e.id, u.nombres, u.apellidos, m.curso_id, c.nombre;

CREATE OR REPLACE VIEW vw_progreso_estudiante AS
SELECT
    m.id AS matricula_id,
    e.id AS estudiante_id,
    CONCAT(u.nombres, ' ', u.apellidos) AS estudiante,
    m.curso_id,
    c.nombre AS curso,
    COUNT(ev.id) AS evaluaciones_total,
    COALESCE(SUM(CASE WHEN cal.id IS NOT NULL THEN 1 ELSE 0 END), 0) AS evaluaciones_calificadas,
    COALESCE(SUM(ev.porcentaje), 0) AS peso_total,
    COALESCE(SUM(CASE WHEN cal.id IS NOT NULL THEN ev.porcentaje ELSE 0 END), 0) AS peso_calificado,
    COALESCE(ROUND(
        SUM(CASE WHEN cal.id IS NOT NULL THEN ev.porcentaje ELSE 0 END)
        * 100.0 / NULLIF(SUM(ev.porcentaje), 0)
    , 1), 0) AS porcentaje_avance,
    ROUND(
        SUM(CASE WHEN cal.id IS NOT NULL THEN cal.nota * ev.porcentaje ELSE 0 END)
        / NULLIF(SUM(CASE WHEN cal.id IS NOT NULL THEN ev.porcentaje ELSE 0 END), 0)
    , 2) AS promedio_ponderado
FROM matriculas m
INNER JOIN estudiantes e ON e.id = m.estudiante_id
INNER JOIN usuarios u ON u.id = e.usuario_id
INNER JOIN cursos c ON c.id = m.curso_id
LEFT JOIN evaluaciones ev ON ev.curso_id = m.curso_id
LEFT JOIN calificaciones cal
    ON cal.evaluacion_id = ev.id AND cal.matricula_id = m.id
GROUP BY m.id, e.id, u.nombres, u.apellidos, m.curso_id, c.nombre;

CREATE OR REPLACE VIEW vw_ingresos_mensuales AS
SELECT
    DATE_FORMAT(p.fecha_pago, '%Y-%m') AS periodo,
    COUNT(CASE WHEN p.estado = 'REGISTRADO' THEN 1 END) AS pagos_registrados,
    COALESCE(SUM(CASE WHEN p.estado = 'REGISTRADO' THEN p.valor ELSE 0 END), 0) AS ingreso_total,
    COUNT(CASE WHEN p.estado = 'ANULADO' THEN 1 END) AS pagos_anulados,
    COALESCE(SUM(CASE WHEN p.estado = 'ANULADO' THEN p.valor ELSE 0 END), 0) AS total_anulado
FROM pagos p
GROUP BY DATE_FORMAT(p.fecha_pago, '%Y-%m')
ORDER BY periodo DESC;

CREATE OR REPLACE VIEW vw_cartera_vencida AS
SELECT
    q.id AS cuota_id,
    m.id AS matricula_id,
    e.id AS estudiante_id,
    CONCAT(u.nombres, ' ', u.apellidos) AS estudiante,
    u.email,
    c.id AS curso_id,
    c.nombre AS curso,
    q.numero,
    q.concepto,
    q.periodo,
    q.valor,
    COALESCE(SUM(CASE WHEN p.estado = 'REGISTRADO' THEN p.valor ELSE 0 END), 0) AS pagado,
    ROUND(q.valor - COALESCE(SUM(CASE WHEN p.estado = 'REGISTRADO' THEN p.valor ELSE 0 END), 0), 2) AS saldo,
    q.fecha_vencimiento,
    DATEDIFF(CURDATE(), q.fecha_vencimiento) AS dias_mora
FROM cuotas q
INNER JOIN matriculas m ON m.id = q.matricula_id
INNER JOIN estudiantes e ON e.id = m.estudiante_id
INNER JOIN usuarios u ON u.id = e.usuario_id
INNER JOIN cursos c ON c.id = m.curso_id
LEFT JOIN pagos p ON p.cuota_id = q.id
WHERE q.estado <> 'ANULADA'
  AND q.fecha_vencimiento < CURDATE()
GROUP BY q.id, m.id, e.id, u.nombres, u.apellidos, u.email,
         c.id, c.nombre, q.numero, q.concepto, q.periodo, q.valor, q.fecha_vencimiento
HAVING saldo > 0
ORDER BY dias_mora DESC;

-- ---------------------------------------------------------------------
-- Fin del esquema
-- ---------------------------------------------------------------------
