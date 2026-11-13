# 5. Matriz de trazabilidad

## 5.1 Criterio

La matriz garantiza que **todo requisito** tenga al menos una historia de usuario, un caso de uso, una tabla donde persistir, una ruta/controlador que lo implemente y una prueba que lo verifique. Si una fila queda incompleta, el requisito no se considera listo para desarrollo.

**Leyenda de pruebas:** `CP-xx` = caso de prueba funcional. Los casos críticos se automatizan en `tests/run_tests.php` y el resto se ejecuta en la lista de verificación de sustentación.

## 5.2 Matriz RF → HU → CU → persistencia → implementación → prueba

| RF | HU | CU | Tabla(s) / vista | Ruta y controlador | Prueba |
|---|---|---|---|---|---|
| RF-01 | HU-01 | CU-01 | usuarios, roles, auditoria | `POST /login` · `AuthController@login` | CP-01 |
| RF-02 | HU-01 | CU-01 | — (sesión) | `POST /logout` · `AuthController@logout` | CP-02 |
| RF-03 | HU-02 | CU-02 | usuarios, auditoria | `/password/recuperar` · `PasswordController` | CP-03 |
| RF-04 | HU-01 | CU-02 | usuarios | `/password/cambiar` · `PasswordController@change` | CP-04 |
| RF-05 | HU-03 | CU-03 | roles, permisos, rol_permiso | `Auth::can()` · middleware de controlador | CP-05 |
| RF-06 | HU-04 | CU-03 | usuarios, estudiantes, profesores | `/usuarios` · `UsuarioController` | CP-06 |
| RF-07 | HU-03 | CU-03 | roles, permisos, rol_permiso | `/roles` · `RolController` | CP-07 |
| RF-08 | HU-05 | CU-19 | auditoria | `Auditoria::registrar()` | CP-08 |
| RF-09 | HU-06 | CU-04 | niveles | `/niveles` · `NivelController` | CP-09 |
| RF-10 | HU-07 | CU-04 | cursos, niveles, profesores | `/cursos` · `CursoController` | CP-10 |
| RF-11 | HU-07 | CU-04 | cursos, horarios | `CursoController@asignarProfesor` | CP-11 |
| RF-12 | HU-08 | CU-05 | horarios, cursos | `/horarios` · `HorarioController` | CP-12 |
| RF-13 | HU-07 | CU-04 | profesores, usuarios | `/profesores` · `ProfesorController` | CP-13 |
| RF-14 | HU-09 | CU-05 | vw_cursos_detalle, horarios | `/agenda` · `AgendaController` | CP-14 |
| RF-15 | HU-09 | CU-05 | sesiones_clase | `/sesiones` · `SesionController` | CP-15 |
| RF-16 | HU-07 | CU-04 | cursos | `CursoController@cambiarEstado` | CP-16 |
| RF-17 | HU-10 | CU-06 | usuarios, estudiantes | `/estudiantes` · `EstudianteController@store` | CP-17 |
| RF-18 | HU-12 | CU-08 | vw_progreso, vw_estado_cuenta, vw_asistencia | `EstudianteController@show` | CP-18 |
| RF-19 | HU-11 | CU-07 | matriculas, cursos | `POST /matriculas` · `MatriculaController@store` | CP-19 |
| RF-20 | HU-11 | CU-07 | cuotas, matriculas | `Matricula::generarCuotas()` | CP-20 |
| RF-21 | HU-14 | CU-07 | matriculas, auditoria | `MatriculaController@retirar` | CP-21 |
| RF-22 | HU-13 | CU-08 | vw_estudiantes | `EstudianteController@index` | CP-22 |
| RF-23 | HU-10 | CU-06 | estudiantes | `EstudianteController@update` | CP-23 |
| RF-24 | HU-15 | CU-09 | pagos | `POST /pagos` · `PagoController@store` | CP-24 |
| RF-25 | HU-15 | CU-09 | pagos, cuotas | `Pago::registrar()` (transacción) | CP-25 |
| RF-26 | HU-17 | CU-10 | pagos, cuotas, auditoria | `PagoController@anular` | CP-26 |
| RF-27 | HU-16 | CU-11 | vw_estado_cuenta | `/estado-cuenta` · `EstadoCuentaController` | CP-27 |
| RF-28 | HU-15 | CU-09 | pagos, configuracion | `PagoController@comprobante` | CP-28 |
| RF-29 | HU-18 | CU-18 | pagos, vw_ingresos_mensuales | `ReporteController@ingresos` | CP-29 |
| RF-30 | HU-18 | CU-18 | cuotas, vw_estado_cuenta | `ReporteController@cartera` | CP-30 |
| RF-31 | HU-19 | CU-12 | sesiones_clase, asistencias | `AsistenciaController@guardar` | CP-31 |
| RF-32 | HU-19 | CU-12 | asistencias, auditoria | `AsistenciaController@actualizar` | CP-32 |
| RF-33 | HU-19 | CU-12 | vw_asistencia_estudiante | `ProgresoController@asistencia` | CP-33 |
| RF-34 | HU-20 | CU-13 | evaluaciones | `/evaluaciones` · `EvaluacionController` | CP-34 |
| RF-35 | HU-21 | CU-14 | calificaciones, auditoria | `/calificaciones` · `CalificacionController` | CP-35 |
| RF-36 | HU-22 | CU-15 | vw_progreso_estudiante | `/progreso` · `ProgresoController@show` | CP-36 |
| RF-37 | HU-23 | CU-16 | alertas, evaluaciones | `AlertaService::evaluacionesProximas()` | CP-37 |
| RF-38 | HU-23 | CU-16 | alertas, calificaciones | `AlertaService::pendientes()` | CP-38 |
| RF-39 | HU-23, HU-24 | CU-16 | alertas, vw_progreso, vw_asistencia | `AlertaService::riesgo()` · `AlertaController` | CP-39 |
| RF-40 | HU-25, 26, 27 | CU-17 | vistas de resumen | `/dashboard` · `DashboardController@index` | CP-40 |
| RF-41 | HU-28 | CU-18 | vw_estudiantes, matriculas | `ReporteController@matriculas` | CP-41 |
| RF-42 | HU-28 | CU-18 | vw_asistencia_estudiante | `ReporteController@asistencia` | CP-42 |
| RF-43 | HU-28 | CU-18 | pagos, cuotas | `ReporteController@finanzas` | CP-43 |
| RF-44 | HU-05, HU-29 | CU-19 | auditoria | `AuditoriaController@index` | CP-44 |
| RF-45 | HU-28 | CU-18 | vistas de reporte | `CsvExporter::stream()` | CP-45 |
| RF-46 | HU-30 | — | configuracion | `/configuracion` · `ConfiguracionController` | CP-46 |

**Cobertura:** 46/46 requisitos funcionales con historia, caso de uso, persistencia, implementación y prueba.

## 5.3 Trazabilidad inversa HU → RF

| HU | RF cubiertos | Sprint |
|---|---|---|
| HU-01 | RF-01, RF-02 | S1 |
| HU-02 | RF-03 | S1 |
| HU-03 | RF-05, RF-07 | S1 |
| HU-04 | RF-06 | S1 |
| HU-05 | RF-08 | S1 |
| HU-06 | RF-09 | S2 |
| HU-07 | RF-10, RF-11, RF-13, RF-16 | S2 |
| HU-08 | RF-12 | S2 |
| HU-09 | RF-14, RF-15 | S2 |
| HU-10 | RF-17, RF-23 | S3 |
| HU-11 | RF-19, RF-20 | S3 |
| HU-12 | RF-18 | S3 |
| HU-13 | RF-22 | S3 |
| HU-14 | RF-21 | S3 |
| HU-15 | RF-24, RF-25, RF-28 | S4 |
| HU-16 | RF-27 | S4 |
| HU-17 | RF-26 | S4 |
| HU-18 | RF-29, RF-30 | S4 |
| HU-19 | RF-31, RF-32, RF-33 | S5 |
| HU-20 | RF-34 | S5 |
| HU-21 | RF-35 | S5 |
| HU-22 | RF-36 | S5 |
| HU-23 | RF-37, RF-38, RF-39 | S5 |
| HU-24 | RF-39 | S5 |
| HU-25 | RF-40 | S6 |
| HU-26 | RF-40 | S6 |
| HU-27 | RF-40 | S6 |
| HU-28 | RF-41, RF-42, RF-43, RF-45 | S6 |
| HU-29 | RF-44 | S6 |
| HU-30 | RF-46 | S6 |

## 5.4 Cobertura por tabla

| Tabla | RF que la usan | Módulo |
|---|---|---|
| `roles`, `permisos`, `rol_permiso` | RF-05, 07 | Seguridad |
| `usuarios` | RF-01, 06, 08, 17, 24, 31, 35 | Seguridad / operación |
| `estudiantes` | RF-17 a 23 | Matrículas |
| `profesores` | RF-10, 11, 13, 14 | Académico |
| `niveles` | RF-09, 10, 41 | Académico |
| `cursos` | RF-10 a 16, 19 | Académico |
| `horarios` | RF-11, 12, 14 | Académico |
| `matriculas` | RF-19 a 22, 27, 31, 36 | Matrículas |
| `cuotas` | RF-20, 25, 26, 27, 30 | Finanzas |
| `pagos` | RF-24 a 30 | Finanzas |
| `sesiones_clase` | RF-15, 31, 32, 33 | Asistencia |
| `asistencias` | RF-31 a 33, 39 | Asistencia |
| `evaluaciones` | RF-34, 37, 38 | Progreso |
| `calificaciones` | RF-35, 36, 39 | Progreso |
| `alertas` | RF-37 a 39 | Innovación |
| `auditoria` | RF-08, 44 | Auditoría |
| `configuracion` | RF-28, 46 | Configuración |

## 5.5 Casos de prueba críticos (smoke test de sustentación)

| CP | Escenario | Resultado esperado |
|---|---|---|
| CP-01 | Login admin con credenciales válidas | Redirección a `/dashboard`; sesión creada. |
| CP-02 | Logout y acceso posterior al dashboard | Redirección a `/login`. |
| CP-05 | Estudiante invoca `/usuarios` | HTTP 403 y auditoría del intento. |
| CP-08 | Login exitoso | Registro en `auditoria` con IP y fecha. |
| CP-11 | Asignar profesor con choque de horario | Rechazo con curso en conflicto. |
| CP-19 | Matricular en curso lleno | Rechazo con mensaje de cupo. |
| CP-20 | Matrícula de 1.200.000 en 4 cuotas | 4 cuotas de 300.000; suma exacta. |
| CP-25 | Pago total de una cuota | Cuota PAGADA y saldo cero. |
| CP-26 | Anular pago | Pago ANULADO; cuota PENDIENTE; ingreso neto actualizado. |
| CP-31 | Asistencia de 20 estudiantes | 20 filas únicas; resumen correcto. |
| CP-35 | Nota 6.0 | Rechazo por rango 1.0–5.0. |
| CP-36 | Notas 4.0 (30 %), 3.5 (20 %), 50 % pendiente | Promedio 3.8; avance 50 %. |
| CP-37 | Evaluación a 5 días | Alerta única por estudiante. |
| CP-39 | Promedio 2.8 | Alerta de bajo rendimiento. |
| CP-45 | Exportar CSV | Archivo UTF-8 con encabezados. |

## 5.6 Requisitos no funcionales → verificación

| RNF | Evidencia |
|---|---|
| RNF-01, 02, 03, 04, 05, 06 | Revisión de código en `AuthController`, `Database`, `Csrf`, `Auth` y pruebas CP-01/02/05. |
| RNF-07 | Validación JS en `public/js/login.js` + `Validator` en PHP. |
| RNF-09, 10 | `EXPLAIN` de consultas y pruebas de carga con 1.000 estudiantes. |
| RNF-11, 12, 13 | Prueba responsive en 360, 768 y 1366 px; navegación por teclado. |
| RNF-14, 16 | Instalación en XAMPP y verificación en `php -v` / MySQL 8. |
| RNF-15, 20 | Inspección de estructura + `php tests/run_tests.php`. |
| RNF-17, 18 | Plan de respaldo y aviso de privacidad en el login. |
| RNF-19 | Transacciones en matrícula y pago (CP-20, CP-25, CP-26). |
| RNF-21, 22 | Configuración `America/Bogota`, formato COP y log de errores. |
