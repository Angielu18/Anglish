# 2. Especificación de requisitos (ISO/IEC/IEEE 29148)

## 2.1 Plantilla de especificación

Cada requisito funcional (RF) se documenta con los atributos exigidos por ISO/IEC/IEEE 29148:

| Atributo | Uso en Anglish |
|---|---|
| **Identificador** | `RF-xx` único y estable; no se reutiliza. |
| **Prioridad** | MoSCoW: `M` (Must), `S` (Should), `C` (Could), `W` (Won't). |
| **Actor** | Administrador, Profesor, Estudiante o Sistema. |
| **Descripción** | Necesidad observable, sin detalles de implementación. |
| **Criterio de aceptación** | Condición verificable y medible. |
| **Método de verificación** | `P` prueba funcional, `I` inspección de código/configuración, `A` análisis. |
| **Fuente** | Entrevista con la coordinadora, observación del proceso actual y guía del proyecto ADSO. |

## 2.2 Actores y matriz de permisos

| Capacidad | Administrador | Profesor | Estudiante |
|---|:---:|:---:|:---:|
| Usuarios, roles y permisos | ✅ | — | — |
| Niveles, cursos, horarios y profesores | ✅ | Consulta | Consulta |
| Estudiantes y matrículas | ✅ | Consulta de sus cursos | Solo su ficha |
| Pagos y cartera | ✅ | — | Solo su estado de cuenta |
| Asistencia | ✅ | ✅ (sus cursos) | Solo su asistencia |
| Evaluaciones y notas | ✅ | ✅ (sus cursos) | Solo sus notas |
| Alertas | ✅ | ✅ | ✅ |
| Reportes y auditoría | ✅ | Reportes de sus cursos | — |

## 2.3 Requisitos funcionales

### M1. Autenticación, usuarios y RBAC

| ID | Requisito | Actor | Prio | Criterio de aceptación | Verif. |
|---|---|---|---|---|---|
| RF-01 | Iniciar sesión con correo y contraseña, validando estado del usuario y creando sesión segura. | Todos | M | Credenciales válidas → dashboard del rol; inválidas → mensaje genérico sin revelar si el correo existe. | P |
| RF-02 | Cerrar sesión destruyendo la sesión, la cookie y regenerando el identificador. | Todos | M | Tras logout, una petición al dashboard redirige al login y la cookie ya no es válida. | P |
| RF-03 | Recuperar contraseña con token de un solo uso y vigencia de 30 minutos. | Todos | S | Token usado o vencido → rechazo; token válido → permite definir nueva clave. | P |
| RF-04 | Cambiar la contraseña autenticado, exigiendo la clave actual y validando fortaleza. | Todos | M | Clave actual incorrecta → error; nueva clave débil → error con reglas visibles. | P |
| RF-05 | Aplicar RBAC en menús, rutas y acciones del servidor (denegar por defecto). | Sistema | M | Un estudiante que invoca una URL de administración recibe 403 y el intento se audita. | P/I |
| RF-06 | Administrar usuarios: crear, editar, activar, inactivar y restablecer clave. | Administrador | M | No se permiten correos ni documentos duplicados; el usuario inactivo no puede iniciar sesión. | P |
| RF-07 | Asignar rol y permisos a cada usuario desde la interfaz. | Administrador | S | El cambio de permisos surte efecto en la siguiente petición sin reiniciar sesión. | P |
| RF-08 | Auditar accesos y acciones críticas con usuario, acción, módulo, IP y fecha. | Sistema | M | Login, logout, matrícula, pago, anulación, nota y asistencia generan registro de auditoría. | P/I |

### M2. Gestión académica

| ID | Requisito | Actor | Prio | Criterio de aceptación | Verif. |
|---|---|---|---|---|---|
| RF-09 | Gestionar niveles A1–C1 con código, nombre, orden, descripción y horas. | Administrador | M | No se permiten códigos duplicados; el orden es único y editable. | P |
| RF-10 | Gestionar cursos con nivel, profesor, jornada, aula, cupo, fechas y estado. | Administrador | M | Curso sin nivel o profesor no se guarda; el código de curso es único. | P |
| RF-11 | Asignar profesor a curso validando que no tenga otra clase en el mismo horario. | Administrador | M | Solapamiento detectado → mensaje con el curso en conflicto y no se guarda. | P |
| RF-12 | Gestionar horarios por curso con día, hora inicial y final. | Administrador | M | Hora final ≤ inicial → error; dos horarios del mismo curso no se solapan. | P |
| RF-13 | Gestionar profesores con especialidad, título y fecha de contratación. | Administrador | M | Profesor sin usuario asociado no se crea; se puede consultar su carga académica. | P |
| RF-14 | Consultar la agenda semanal del profesor con cursos, aulas y horarios. | Profesor | M | La agenda muestra únicamente los cursos asignados al profesor autenticado. | P |
| RF-15 | Programar sesiones de clase por curso con fecha, hora, tema y estado. | Administrador | M | No se duplica la misma sesión (curso, fecha y hora); se puede cancelar con motivo. | P |
| RF-16 | Cambiar el estado de un curso (planificado, en curso, finalizado, cancelado). | Administrador | S | Un curso cancelado no admite nuevas matrículas ni registros de asistencia. | P |

### M3. Estudiantes y matrículas

| ID | Requisito | Actor | Prio | Criterio de aceptación | Verif. |
|---|---|---|---|---|---|
| RF-17 | Registrar y editar estudiantes creando su usuario y datos personales. | Administrador | M | Documento y correo únicos; contraseña inicial cifrada; estudiante queda ACTIVO. | P |
| RF-18 | Consultar la ficha 360° del estudiante (académica, financiera, asistencia y alertas). | Admin/Profesor | M | La ficha consolida curso, promedio, asistencia, saldo y alertas en una vista. | P |
| RF-19 | Matricular un estudiante en un curso validando cupo, estado del curso y duplicidad. | Administrador | M | Curso lleno, cancelado o matrícula activa duplicada → rechazo con motivo. | P |
| RF-20 | Generar automáticamente las cuotas de la matrícula según valor total y número de cuotas. | Sistema | M | Se crean N cuotas cuya suma es exactamente el valor total; fechas mensuales consecutivas. | P |
| RF-21 | Retirar una matrícula con motivo, conservando el historial y la cartera pendiente. | Administrador | S | El estado pasa a RETIRADA, libera el cupo y genera registro de auditoría. | P |
| RF-22 | Listar y filtrar estudiantes por nivel, curso, estado y texto libre. | Administrador | M | Los filtros se combinan y el resultado se pagina sin recargar toda la tabla. | P |
| RF-23 | Registrar y actualizar datos del acudiente. | Administrador | C | Nombre y teléfono del acudiente se guardan y se muestran en la ficha. | P |

### M4. Pagos y finanzas

| ID | Requisito | Actor | Prio | Criterio de aceptación | Verif. |
|---|---|---|---|---|---|
| RF-24 | Registrar pagos de matrícula, mensualidad, certificado u otro, con método y referencia. | Administrador | M | Valor > 0 y fecha válida; el pago queda REGISTRADO y auditado. | P |
| RF-25 | Aplicar un pago a una cuota y actualizar su estado a PAGADA si cubre el saldo. | Sistema | M | Pago parcial conserva saldo; pago total marca la cuota como PAGADA en la misma transacción. | P |
| RF-26 | Anular un pago con motivo, sin eliminarlo físicamente, y recalcular la cuota. | Administrador | M | Pago ANULADO deja de sumar ingresos y la cuota vuelve a PENDIENTE si queda saldo. | P |
| RF-27 | Consultar el estado de cuenta por estudiante: total, pagado, saldo y cuotas vencidas. | Admin/Estudiante | M | El estado (AL DÍA, PENDIENTE, EN MORA) coincide con las cuotas y fechas reales. | P |
| RF-28 | Generar un comprobante de pago imprimible con datos de la academia y del pago. | Administrador | S | El comprobante incluye número, fecha, estudiante, concepto, valor y método. | P |
| RF-29 | Reportar ingresos por periodo, método de pago y concepto. | Administrador | M | La suma del reporte coincide con los pagos REGISTRADOS del rango seleccionado. | P |
| RF-30 | Identificar y alertar la cartera vencida por estudiante y curso. | Sistema | M | Toda cuota con fecha de vencimiento anterior a hoy y saldo > 0 aparece en el reporte. | P |

### M5. Funcionalidad innovadora: asistencia, progreso y alertas

| ID | Requisito | Actor | Prio | Criterio de aceptación | Verif. |
|---|---|---|---|---|---|
| RF-31 | Registrar asistencia por sesión con estados PRESENTE, AUSENTE, TARDE y EXCUSA. | Profesor | M | Se guarda una asistencia por matrícula y sesión; la interfaz permite marcar todos y ajustar casos. | P |
| RF-32 | Editar la asistencia dentro de las 24 horas siguientes, dejando traza de quién y cuándo. | Profesor | S | Fuera de la ventana solo el administrador puede editar; todo cambio queda auditado. | P |
| RF-33 | Calcular el porcentaje de asistencia por matrícula y curso. | Sistema | M | % = (sesiones − ausentes) / sesiones × 100, redondeado a un decimal. | P |
| RF-34 | Gestionar evaluaciones con tipo, peso porcentual y fecha de entrega. | Profesor | M | La suma de pesos por curso no puede superar 100; se advierte si no llega a 100. | P |
| RF-35 | Registrar calificaciones en escala 1.0–5.0 para estudiantes matriculados. | Profesor | M | Nota fuera de rango o matrícula inactiva → rechazo; nota existente se actualiza con auditoría. | P |
| RF-36 | Calcular promedio ponderado y porcentaje de avance académico. | Sistema | M | Promedio = Σ(nota×peso)/Σ(peso calificado); avance = Σ(peso calificado)%. | P |
| RF-37 | Generar alertas de evaluaciones y entregas próximas (7 días) para estudiantes y profesores. | Sistema | M | Una evaluación a ≤7 días genera alerta única por usuario y evento. | P |
| RF-38 | Generar alertas de evaluaciones vencidas sin calificar y de actividades pendientes. | Sistema | M | El profesor recibe alerta de evaluaciones vencidas sin notas; el estudiante, de entregas pendientes. | P |
| RF-39 | Generar alertas de bajo rendimiento (promedio < 3.0), inasistencia (< 80 %) y cartera vencida, con centro de notificaciones. | Sistema | M | Cada condición genera máximo una alerta vigente por estudiante y tipo; se puede marcar como leída. | P |

### M6. Dashboard, reportes y configuración

| ID | Requisito | Actor | Prio | Criterio de aceptación | Verif. |
|---|---|---|---|---|---|
| RF-40 | Mostrar un dashboard diferenciado por rol con métricas clave. | Todos | M | Admin: estudiantes, ingresos, cartera, asistencia; profesor: cursos y pendientes; estudiante: progreso y saldo. | P |
| RF-41 | Reportar estudiantes activos y matrículas por nivel y curso. | Administrador | M | El total por nivel coincide con las matrículas ACTIVAS del periodo. | P |
| RF-42 | Reportar asistencia e inasistencias por curso y rango de fechas. | Admin/Profesor | M | Se muestran totales por estado y porcentaje; el profesor solo ve sus cursos. | P |
| RF-43 | Reportar ingresos, cartera y pagos anulados por periodo. | Administrador | M | Los anulados se muestran separados y no contaminan los ingresos netos. | P |
| RF-44 | Filtrar la auditoría por usuario, módulo, acción y rango de fechas. | Administrador | S | Los filtros se combinan; el resultado está ordenado del más reciente al más antiguo. | P |
| RF-45 | Exportar reportes a CSV con codificación UTF-8 y nombres descriptivos. | Administrador | S | El archivo se descarga, abre en Excel sin caracteres corruptos e incluye encabezados. | P |
| RF-46 | Configurar datos de la academia, moneda, umbrales de alerta y periodo académico. | Administrador | C | Los cambios se reflejan en comprobantes, alertas y dashboard sin tocar el código. | P |

## 2.4 Reglas de negocio

| ID | Regla |
|---|---|
| RN-01 | El correo y el documento de identidad son únicos en `usuarios`. |
| RN-02 | La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula, número y símbolo. |
| RN-03 | Tras 5 intentos fallidos consecutivos, la cuenta se bloquea 15 minutos. |
| RN-04 | Un curso no admite matrículas ACTIVAS por encima de su `cupo_maximo`. |
| RN-05 | Un estudiante no puede tener dos matrículas ACTIVAS en el mismo curso. |
| RN-06 | La suma de porcentajes de evaluaciones de un curso debe ser exactamente 100 para cerrar el periodo. |
| RN-07 | Las notas van de 1.0 a 5.0 con un decimal; se aprueba con 3.0 o más. |
| RN-08 | La asistencia efectiva incluye PRESENTE, TARDE y EXCUSA; solo AUSENTE resta. |
| RN-09 | Se requiere ≥ 80 % de asistencia para aprobar el curso. |
| RN-10 | Una cuota está vencida desde el día siguiente a `fecha_vencimiento` si su saldo es mayor a cero. |
| RN-11 | Un pago no puede aplicarse por encima del saldo de la cuota. |
| RN-12 | Los pagos y matrículas no se eliminan: se anulan o retiran conservando historial. |
| RN-13 | Solo el administrador puede anular pagos, retirar matrículas y gestionar usuarios. |
| RN-14 | El avance académico es el porcentaje del peso total ya calificado; el promedio solo considera evaluaciones calificadas. |
| RN-15 | Las alertas se deduplican por usuario, tipo y referencia para no saturar al usuario. |
| RN-16 | Las fechas se almacenan en formato MySQL y se muestran en `dd/mm/aaaa`; la zona horaria es America/Bogota. |
| RN-17 | Los valores monetarios se guardan en COP con dos decimales y se muestran sin decimales. |
| RN-18 | Un profesor no puede tener dos cursos con horarios solapados. |
| RN-19 | Solo se registra asistencia a matrículas ACTIVAS de cursos EN_CURSO o FINALIZADO. |
| RN-20 | La matrícula genera cuotas cuya suma debe ser igual al valor total; la diferencia por redondeo se ajusta en la última cuota. |

## 2.5 Requisitos no funcionales (ISO/IEC 25010)

| ID | Categoría | Requisito y métrica | Verif. |
|---|---|---|---|
| RNF-01 | Seguridad | Contraseñas almacenadas con `password_hash()` (bcrypt/Argon2id); prohibido texto plano o MD5/SHA1. | I |
| RNF-02 | Seguridad | El 100 % de las consultas usa PDO con `prepare()` y parámetros vinculados; cero concatenaciones con entrada del usuario. | I |
| RNF-03 | Seguridad | Toda operación POST/PUT/DELETE valida token CSRF; token inválido → 419 y auditoría. | P/I |
| RNF-04 | Seguridad | Cookies de sesión `HttpOnly`, `SameSite=Lax`, `Secure` en producción; regeneración de ID al autenticar; expiración por inactividad de 30 min. | I |
| RNF-05 | Seguridad | RBAC verificado en el servidor en cada acción, no solo ocultando botones. | P/I |
| RNF-06 | Seguridad | Toda salida dinámica se escapa con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; cabeceras CSP, `X-Frame-Options` y `X-Content-Type-Options`. | I |
| RNF-07 | Seguridad | Validación doble: JavaScript para UX y PHP para integridad; el servidor nunca confía en el cliente. | P |
| RNF-08 | Auditoría | La tabla `auditoria` es de solo inserción; no se actualiza ni elimina desde la aplicación. | I |
| RNF-09 | Rendimiento | Dashboard y listados principales responden en < 2 s con 1.000 estudiantes y 50.000 asistencias en un equipo de 8 GB. | P |
| RNF-10 | Rendimiento | Consultas frecuentes con índices en claves foráneas, correo, documento, fechas y estados; sin N+1. | A |
| RNF-11 | Usabilidad | Interfaz responsive de 360 px a 1920 px; sin desplazamiento horizontal en móvil. | P |
| RNF-12 | Usabilidad | Las tareas críticas (matricular, pagar, asistencia, nota) se completan en ≤ 3 pasos y muestran confirmación. | P |
| RNF-13 | Accesibilidad | WCAG 2.1 AA: contraste ≥ 4.5:1, etiquetas asociadas, navegación por teclado y foco visible. | P |
| RNF-14 | Compatibilidad | PHP 8.0+, MySQL 8.0, Apache con `mod_rewrite`; Chrome, Edge y Firefox actuales. | P |
| RNF-15 | Mantenibilidad | MVC estricto, clases < 400 líneas, métodos < 50 líneas, comentarios en español y nombres descriptivos. | I |
| RNF-16 | Portabilidad | Configuración centralizada en `app/config`; funciona en XAMPP (Windows) y LAMP (Linux) sin cambios de código. | P |
| RNF-17 | Disponibilidad | 99 % en horario académico; respaldo diario de la base de datos y recuperación documentada. | A |
| RNF-18 | Privacidad | Cumplir Ley 1581: consentimiento informado, minimización de datos, acceso solo por rol y derecho de actualización/eliminación. | A |
| RNF-19 | Integridad | Matrícula, generación de cuotas y registro de pagos se ejecutan en transacciones con rollback ante error. | P/I |
| RNF-20 | Testabilidad | Suite ejecutable con `php tests/run_tests.php`; cubre validaciones, router, CSRF y reglas de negocio críticas. | P |
| RNF-21 | Localización | Textos en español de Colombia, fechas `dd/mm/aaaa`, moneda COP, zona `America/Bogota`. | I |
| RNF-22 | Observabilidad | Errores registrados en `logs/error.log`; al usuario se muestra un mensaje genérico sin trazas ni SQL. | I |

## 2.6 Priorización MoSCoW

| Prioridad | Requisitos | Total |
|---|---|---:|
| **Must** | RF-01, 02, 04, 05, 06, 08–15, 17–20, 22, 24–27, 29–31, 33–37, 39–43 | 33 |
| **Should** | RF-03, 07, 16, 21, 28, 32, 38, 44, 45 | 9 |
| **Could** | RF-23, 46 | 2 |
| **Won't (v1.0)** | Pasarela de pagos, app nativa, facturación DIAN, multi-sede | — |

## 2.7 Validación y aprobación

- **Revisión de requisitos:** cada RF tiene un criterio de aceptación verificable y una prueba asociada en `docs/05_trazabilidad.md`.
- **Criterio de aprobación:** no existen requisitos huérfanos (sin historia de usuario) ni historias sin requisito.
- **Control de cambios:** todo cambio de alcance se registra en la tabla de versiones del documento y se refleja en la matriz de trazabilidad.

| Versión | Fecha | Cambio | Autora |
|---|---|---|---|
| 1.0 | 2026-11-08 | Línea base de requisitos de Anglish. | Angie V. Sotelo Ramos |
