# 6. Backlog y planificación por sprints

## 6.1 Épicas

| Épica | Nombre | Historias | Puntos | Valor de negocio |
|---|---|---|---:|---|
| E1 | Seguridad, usuarios y RBAC | HU-01 a HU-05 | 19 | Protege la información y separa responsabilidades por rol. |
| E2 | Gestión académica | HU-06 a HU-09 | 20 | Estructura la oferta de cursos y la carga de los profesores. |
| E3 | Estudiantes y matrículas | HU-10 a HU-14 | 24 | Formaliza el ingreso y genera el plan de pagos. |
| E4 | Pagos y finanzas | HU-15 a HU-18 | 19 | Controla ingresos, cartera y comprobantes. |
| E5 | Asistencia, progreso y alertas | HU-19 a HU-24 | 30 | Funcionalidad innovadora: seguimiento y prevención de deserción. |
| E6 | Dashboard, reportes y configuración | HU-25 a HU-30 | 22 | Convierte los datos en decisiones y auditoría. |
| | **Total** | **30** | **134** | |

## 6.2 Calendario de sprints

| Sprint | Fechas (2026) | Objetivo | Historias | Puntos |
|---|---|---|---|---:|
| S0 | 09 nov | Preparación: repositorio, estructura MVC, base de datos y convenciones. | — | — |
| S1 | 10–16 nov | Autenticación segura, RBAC y auditoría base. | HU-01 a HU-05 | 19 |
| S2 | 17–23 nov | Niveles, cursos, horarios, profesores y agenda. | HU-06 a HU-09 | 20 |
| S3 | 24–30 nov | Estudiantes, matrícula transaccional, cuotas y ficha 360°. | HU-10 a HU-14 | 24 |
| S4 | 01–07 dic | Pagos, anulación, estado de cuenta y reporte financiero. | HU-15 a HU-18 | 19 |
| S5 | 09–15 dic | Asistencia en tiempo real, evaluaciones, notas, progreso y alertas. | HU-19 a HU-24 | 30 |
| S6 | 16–22 dic | Dashboards por rol, reportes CSV, auditoría y configuración. | HU-25 a HU-30 | 22 |
| Buffer | 23 dic–10 ene | Pruebas integrales, documentación y ensayo de sustentación. | — | — |

> Capacidad asumida: 20–24 puntos por sprint para una desarrolladora, con dedicación parcial. El sprint 5 se ajusta a 30 puntos porque concentra la funcionalidad innovadora y puede requerir apoyo del instructor.

## 6.3 Riesgos del backlog

| Riesgo | Prob. | Impacto | Mitigación |
|---|---|---|---|
| Subestimar la lógica transaccional de pagos | Media | Alto | Implementar CU-09 con pruebas CP-25/26 antes de la interfaz. |
| Complejidad del motor de alertas | Media | Alto | Empezar con 3 reglas (evaluación, pago, rendimiento) y ampliar. |
| Choques de horario no detectados | Baja | Medio | Validación en PHP + restricción UNIQUE y prueba CP-11. |
| Pérdida de tiempo en diseño | Media | Medio | Usar Bootstrap 5 y una paleta definida; no crear componentes propios innecesarios. |
| Datos de prueba inconsistentes | Media | Medio | Script semilla determinista con IDs explícitos y verificación por vistas. |
| Requisitos cambiantes | Media | Bajo | Control de cambios y MoSCoW; lo nuevo entra como Could. |

## 6.4 Métricas de seguimiento

- **Velocidad:** puntos completados por sprint (meta: 20+).
- **Lead time:** días desde «En progreso» hasta «Hecho».
- **Defectos:** bugs críticos abiertos al cierre de sprint (meta: 0).
- **Cobertura de trazabilidad:** RF con prueba asociada / total (meta: 100 %).
- **Cobertura de pruebas críticas:** CP aprobados / CP ejecutados (meta: 100 %).

## 6.5 Definition of Ready / Done

- **Ready:** RF asignado, criterios Gherkin, diseño de pantalla, datos de prueba, dependencias y estimación.
- **Done:** criterios cumplidos, pruebas manuales y automatizadas, permisos y CSRF verificados, sin SQL concatenado, documentación y trazabilidad actualizadas, demo realizada.

## 6.6 Importación a Jira

El archivo [`backlog_jira.csv`](backlog_jira.csv) se importa en Jira con:

1. **Projects → Create project → Scrum**.
2. **Project settings → External system import → CSV**.
3. Delimiter `,`, encoding `UTF-8`, first row as header.
4. Mapear `Epic Name` a Epic, `Sprint` a Sprint y `Story Points` a Story point estimate.
5. Crear primero las épicas y luego las historias.

Columnas incluidas: `Issue Type, Summary, Description, Priority, Story Points, Epic Name, Sprint, Labels`.

## 6.7 Backlog priorizado (resumen)

| Orden | Historia | Prioridad | Puntos | Sprint |
|---:|---|---|---:|---|
| 1 | HU-01 Inicio y cierre de sesión | Must | 5 | S1 |
| 2 | HU-03 Menú y acciones por rol | Must | 3 | S1 |
| 3 | HU-04 Gestionar usuarios | Must | 5 | S1 |
| 4 | HU-05 Auditar acciones | Must | 3 | S1 |
| 5 | HU-02 Recuperar contraseña | Should | 3 | S1 |
| 6 | HU-06 Niveles | Must | 3 | S2 |
| 7 | HU-07 Cursos y profesores | Must | 8 | S2 |
| 8 | HU-08 Horarios | Must | 5 | S2 |
| 9 | HU-09 Agenda del profesor | Must | 4 | S2 |
| 10 | HU-10 Registrar estudiantes | Must | 5 | S3 |
| 11 | HU-11 Matricular y generar cuotas | Must | 8 | S3 |
| 12 | HU-12 Ficha 360° | Must | 5 | S3 |
| 13 | HU-13 Filtrar estudiantes | Must | 3 | S3 |
| 14 | HU-14 Retirar matrícula | Should | 3 | S3 |
| 15 | HU-15 Registrar pago y comprobante | Must | 5 | S4 |
| 16 | HU-16 Estado de cuenta | Must | 3 | S4 |
| 17 | HU-17 Anular pago | Must | 3 | S4 |
| 18 | HU-18 Ingresos y cartera | Must | 8 | S4 |
| 19 | HU-19 Asistencia en tiempo real | Must | 8 | S5 |
| 20 | HU-20 Evaluaciones | Must | 5 | S5 |
| 21 | HU-21 Calificaciones | Must | 5 | S5 |
| 22 | HU-22 Progreso académico | Must | 5 | S5 |
| 23 | HU-23 Alertas automáticas | Must | 8 | S5 |
| 24 | HU-24 Centro de notificaciones | Should | 3 | S5 |
| 25 | HU-25 Dashboard admin | Must | 5 | S6 |
| 26 | HU-26 Dashboard profesor | Must | 3 | S6 |
| 27 | HU-27 Dashboard estudiante | Must | 5 | S6 |
| 28 | HU-28 Reportes y CSV | Should | 5 | S6 |
| 29 | HU-29 Filtrar auditoría | Should | 3 | S6 |
| 30 | HU-30 Configurar academia | Could | 3 | S6 |
