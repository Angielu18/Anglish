# 3. Historias de usuario

## 3.1 Convenciones

- **Formato:** Como *rol* quiero *funcionalidad* para *beneficio*.
- **Criterios de aceptación:** Gherkin (Dado / Cuando / Entonces), verificables en la sustentación.
- **Prioridad:** Must, Should, Could (alineada con MoSCoW de `docs/02`).
- **Estimación:** puntos de historia (1, 2, 3, 5, 8) y sprint asignado.
- **Riesgo:** probabilidad de retraso o defecto (Bajo, Medio, Alto).

| Sprint | Épica | Historias | Puntos |
|---|---|---|---:|
| S1 | E1 Seguridad y usuarios | HU-01 a HU-05 | 19 |
| S2 | E2 Gestión académica | HU-06 a HU-09 | 20 |
| S3 | E3 Estudiantes y matrículas | HU-10 a HU-14 | 24 |
| S4 | E4 Pagos y finanzas | HU-15 a HU-18 | 19 |
| S5 | E5 Asistencia, progreso y alertas | HU-19 a HU-24 | 30 |
| S6 | E6 Dashboard, reportes y configuración | HU-25 a HU-30 | 22 |
| **Total** | | **30 historias** | **134** |

---

## Épica E1 — Seguridad y usuarios

### HU-01 · Inicio y cierre de sesión
**Como** usuario registrado **quiero** entrar con mi correo y contraseña y poder salir de forma segura **para** acceder a las funciones de mi rol sin exponer mi cuenta.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 1 · **Riesgo:** Alto · **RF:** RF-01, RF-02
- **CA-01:** Dado un usuario ACTIVO con credenciales correctas, cuando envía el formulario, entonces se crea la sesión, se regenera el ID y llega al dashboard de su rol.
- **CA-02:** Dado un correo inexistente o una clave incorrecta, cuando intenta entrar, entonces ve el mensaje «Credenciales inválidas» sin pistas sobre cuál dato falló.
- **CA-03:** Dado un usuario INACTIVO, cuando intenta entrar, entonces se rechaza el acceso y se audita el intento.
- **CA-04:** Dado un usuario autenticado, cuando pulsa «Cerrar sesión», entonces la sesión y la cookie se destruyen y vuelve al login.
- **CA-05:** Dado el formulario de login, cuando se envía sin token CSRF válido, entonces se rechaza con 419.

### HU-02 · Recuperar contraseña
**Como** usuario **quiero** solicitar un enlace de recuperación **para** volver a acceder si olvido mi clave.

- **Prioridad:** Should · **Puntos:** 3 · **Sprint:** 1 · **Riesgo:** Medio · **RF:** RF-03
- **CA-01:** Dado un correo registrado, cuando solicito recuperación, entonces se genera un token de un solo uso con vencimiento de 30 minutos.
- **CA-02:** Dado un token vencido o ya usado, cuando lo abro, entonces veo un mensaje de enlace inválido y la opción de solicitar otro.
- **CA-03:** Dado un correo no registrado, cuando solicito recuperación, entonces la respuesta es genérica y no revela si existe.

### HU-03 · Menú y acciones según mi rol
**Como** usuario **quiero** ver solo las opciones que mi rol permite **para** evitar errores y acceder rápido a lo mío.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 1 · **Riesgo:** Medio · **RF:** RF-05, RF-07
- **CA-01:** Dado un administrador, cuando inicia sesión, entonces ve usuarios, académico, matrículas, pagos, reportes y auditoría.
- **CA-02:** Dado un profesor, cuando inicia sesión, entonces ve sus cursos, asistencia, evaluaciones y alertas; no ve pagos ni usuarios.
- **CA-03:** Dado un estudiante, cuando intenta abrir `/usuarios` por URL, entonces recibe 403 y el intento se audita.

### HU-04 · Gestionar usuarios
**Como** administrador **quiero** crear y editar usuarios, asignar rol y activarlos o inactivarlos **para** mantener actualizado el personal y los estudiantes.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 1 · **Riesgo:** Medio · **RF:** RF-06
- **CA-01:** Dado un correo o documento ya registrado, cuando guardo, entonces el sistema impide duplicados y señala el campo.
- **CA-02:** Dado un usuario nuevo, cuando lo creo, entonces su clave se guarda con `password_hash()` y nunca se muestra en la interfaz.
- **CA-03:** Dado un usuario inactivo, cuando intenta iniciar sesión, entonces se bloquea el acceso.
- **CA-04:** Dado un cambio de rol, cuando guardo, entonces el menú y los permisos cambian en la siguiente petición.

### HU-05 · Auditar acciones críticas
**Como** administrador **quiero** consultar quién hizo qué y cuándo **para** investigar incidentes y cumplir con la trazabilidad.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 1 · **Riesgo:** Bajo · **RF:** RF-08, RF-44
- **CA-01:** Dado un login, matrícula, pago, anulación, nota o asistencia, cuando ocurre, entonces se inserta un registro con usuario, acción, módulo, IP y fecha.
- **CA-02:** Dado el listado de auditoría, cuando filtro por usuario, módulo, acción y rango de fechas, entonces los filtros se combinan correctamente.
- **CA-03:** Dado cualquier usuario, cuando intenta modificar o borrar un registro de auditoría desde la app, entonces no existe esa opción.

---

## Épica E2 — Gestión académica

### HU-06 · Gestionar niveles de inglés
**Como** administrador **quiero** administrar los niveles A1–C1 **para** organizar los cursos según el MCER.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 2 · **Riesgo:** Bajo · **RF:** RF-09
- **CA-01:** Dado un nivel nuevo, cuando guardo código, nombre, orden y horas, entonces aparece disponible para crear cursos.
- **CA-02:** Dado un código duplicado, cuando guardo, entonces el sistema lo rechaza.
- **CA-03:** Dado un nivel con cursos asociados, cuando intento eliminarlo, entonces se impide para no romper la integridad.

### HU-07 · Gestionar cursos y profesores
**Como** administrador **quiero** crear cursos, asignarles nivel, profesor, jornada, aula y cupo **para** ofertar los programas de la academia.

- **Prioridad:** Must · **Puntos:** 8 · **Sprint:** 2 · **Riesgo:** Alto · **RF:** RF-10, RF-11, RF-13
- **CA-01:** Dado un curso sin nivel o sin profesor, cuando guardo, entonces se rechaza con mensajes por campo.
- **CA-02:** Dado un profesor con clase en el mismo día y hora, cuando lo asigno a otro curso, entonces el sistema muestra el conflicto y no guarda.
- **CA-03:** Dado un curso con cupo 20 y 20 matrículas activas, cuando intento matricular el 21, entonces se rechaza.
- **CA-04:** Dado un curso cancelado, cuando intento matricular, entonces se impide.

### HU-08 · Gestionar horarios
**Como** administrador **quiero** definir los días y horas de cada curso **para** construir la agenda académica sin cruces.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 2 · **Riesgo:** Medio · **RF:** RF-12, RF-18 (RN)
- **CA-01:** Dado un horario con hora final menor o igual a la inicial, cuando guardo, entonces se rechaza.
- **CA-02:** Dado dos horarios del mismo curso que se solapan, cuando guardo el segundo, entonces se rechaza.
- **CA-03:** Dado un curso con varios días, cuando consulto el detalle, entonces veo todos sus horarios ordenados.

### HU-09 · Consultar mi agenda
**Como** profesor **quiero** ver mi agenda semanal **para** preparar mis clases y evitar cruces.

- **Prioridad:** Must · **Puntos:** 4 · **Sprint:** 2 · **Riesgo:** Bajo · **RF:** RF-14, RF-15
- **CA-01:** Dado un profesor autenticado, cuando abre su agenda, entonces ve solo sus cursos con día, hora y aula.
- **CA-02:** Dado un día sin clases, cuando consulto, entonces ve un estado vacío claro, no una tabla en blanco.
- **CA-03:** Dado una sesión programada, cuando la abro, entonces puedo iniciar el registro de asistencia.

---

## Épica E3 — Estudiantes y matrículas

### HU-10 · Registrar estudiantes
**Como** administrador **quiero** registrar estudiantes con sus datos personales y de acudiente **para** vincularlos a los cursos.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 3 · **Riesgo:** Medio · **RF:** RF-17, RF-23
- **CA-01:** Dado un estudiante nuevo, cuando guardo datos válidos, entonces se crea su usuario con rol ESTUDIANTE y clave temporal cifrada.
- **CA-02:** Dado un documento o correo duplicado, cuando guardo, entonces se rechaza y se resalta el campo.
- **CA-03:** Dado un estudiante menor de edad, cuando registro, entonces el acudiente y su teléfono son obligatorios.

### HU-11 · Matricular en un curso
**Como** administrador **quiero** matricular un estudiante y generar sus cuotas **para** formalizar su ingreso y su plan de pagos.

- **Prioridad:** Must · **Puntos:** 8 · **Sprint:** 3 · **Riesgo:** Alto · **RF:** RF-19, RF-20
- **CA-01:** Dado un curso con cupo disponible, cuando matriculo, entonces se crea la matrícula ACTIVA y se descuenta el cupo disponible.
- **CA-02:** Dado un valor total de 1.200.000 COP y 4 cuotas, cuando matriculo, entonces se crean 4 cuotas de 300.000 COP con vencimientos mensuales.
- **CA-03:** Dado un valor que no divide exacto, cuando genero cuotas, entonces la última absorbe el ajuste y la suma sigue siendo igual al total.
- **CA-04:** Dado un estudiante ya matriculado en el curso, cuando repito la operación, entonces se rechaza.
- **CA-05:** Dado un fallo al crear una cuota, cuando ocurre, entonces toda la matrícula se revierte (transacción).

### HU-12 · Consultar ficha 360° del estudiante
**Como** administrador o profesor **quiero** ver en una sola pantalla el estado académico, financiero y de asistencia **para** tomar decisiones informadas.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 3 · **Riesgo:** Medio · **RF:** RF-18
- **CA-01:** Dado un estudiante, cuando abro su ficha, entonces veo curso, nivel, promedio, % de avance, % de asistencia, saldo y alertas.
- **CA-02:** Dado un profesor, cuando abre la ficha, entonces solo ve estudiantes de sus cursos y no información de pagos.
- **CA-03:** Dado un estudiante sin notas, cuando abro la ficha, entonces el progreso muestra 0 % sin errores de división.

### HU-13 · Filtrar y buscar estudiantes
**Como** administrador **quiero** filtrar por nivel, curso, estado y texto **para** encontrar rápido a un estudiante.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 3 · **Riesgo:** Bajo · **RF:** RF-22
- **CA-01:** Dado un texto en el buscador, cuando escribo nombre, apellido o documento, entonces la tabla se filtra.
- **CA-02:** Dado varios filtros, cuando los aplico, entonces se combinan y muestran el total de resultados.
- **CA-03:** Dado un resultado vacío, cuando no hay coincidencias, entonces veo un mensaje claro y puedo limpiar filtros.

### HU-14 · Retirar una matrícula
**Como** administrador **quiero** retirar una matrícula con motivo **para** liberar el cupo sin perder el historial.

- **Prioridad:** Should · **Puntos:** 3 · **Sprint:** 3 · **Riesgo:** Medio · **RF:** RF-21
- **CA-01:** Dado una matrícula ACTIVA, cuando la retiro con motivo, entonces pasa a RETIRADA y el cupo se libera.
- **CA-02:** Dado una matrícula con saldo pendiente, cuando la retiro, entonces el sistema muestra el saldo y lo conserva para cobro.
- **CA-03:** Dado un retiro, cuando consulto auditoría, entonces aparece quién lo hizo, cuándo y por qué.

---

## Épica E4 — Pagos y finanzas

### HU-15 · Registrar un pago y emitir comprobante
**Como** administrador **quiero** registrar pagos de matrícula o mensualidad y generar un comprobante **para** mantener la cartera al día y entregar soporte al estudiante.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 4 · **Riesgo:** Alto · **RF:** RF-24, RF-25, RF-28
- **CA-01:** Dado una cuota pendiente, cuando registro un pago igual a su saldo, entonces la cuota queda PAGADA y el saldo en cero.
- **CA-02:** Dado un pago parcial, cuando lo registro, entonces la cuota conserva el saldo restante.
- **CA-03:** Dado un pago mayor al saldo de la cuota, cuando lo registro, entonces se rechaza.
- **CA-04:** Dado un pago registrado, cuando abro el comprobante, entonces veo número, fecha, estudiante, concepto, valor, método y datos de la academia.
- **CA-05:** Dado un fallo al aplicar el pago, cuando ocurre, entonces la cuota y el pago se revierten juntos.

### HU-16 · Consultar mi estado de cuenta
**Como** estudiante **quiero** ver cuánto he pagado y cuánto debo **para** saber si estoy al día.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 4 · **Riesgo:** Medio · **RF:** RF-27
- **CA-01:** Dado un estudiante autenticado, cuando abre su estado de cuenta, entonces ve total, pagado, saldo y cuotas con vencimiento.
- **CA-02:** Dado una cuota vencida con saldo, cuando consulto, entonces el estado es EN MORA y la cuota se resalta.
- **CA-03:** Dado un estudiante sin cuotas, cuando consulto, entonces ve un estado vacío informativo.

### HU-17 · Anular un pago
**Como** administrador **quiero** anular un pago con motivo **para** corregir errores sin borrar la historia financiera.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 4 · **Riesgo:** Medio · **RF:** RF-26
- **CA-01:** Dado un pago registrado, cuando lo anulo con motivo, entonces pasa a ANULADO y deja de contar en ingresos.
- **CA-02:** Dado una cuota que estaba PAGADA por ese pago, cuando anulo, entonces vuelve a PENDIENTE.
- **CA-03:** Dado un pago anulado, cuando consulto reportes, entonces aparece en la sección de anulados y no en el ingreso neto.

### HU-18 · Reportar ingresos y cartera
**Como** administrador **quiero** ver ingresos por periodo y la cartera pendiente **para** controlar la salud financiera de la academia.

- **Prioridad:** Must · **Puntos:** 8 · **Sprint:** 4 · **Riesgo:** Medio · **RF:** RF-29, RF-30
- **CA-01:** Dado un rango de fechas, cuando genero el reporte, entonces la suma coincide con los pagos REGISTRADOS del rango.
- **CA-02:** Dado el reporte de cartera, cuando lo abro, entonces veo estudiantes con saldo, cuotas vencidas y días de mora.
- **CA-03:** Dado un pago anulado, cuando genero ingresos, entonces no se suma.

---

## Épica E5 — Asistencia, progreso y alertas (funcionalidad innovadora)

### HU-19 · Tomar asistencia en tiempo real
**Como** profesor **quiero** registrar la asistencia de una sesión desde mi celular o computador **para** reflejar la realidad del aula al instante.

- **Prioridad:** Must · **Puntos:** 8 · **Sprint:** 5 · **Riesgo:** Alto · **RF:** RF-31, RF-32, RF-33
- **CA-01:** Dada una sesión con 20 estudiantes activos, cuando abro el registro, entonces veo la lista completa con PRESENTE por defecto.
- **CA-02:** Dado un estudiante ausente, cuando marco AUSENTE y guardo, entonces la asistencia se registra una sola vez por sesión.
- **CA-03:** Dado un registro guardado, cuando lo edito dentro de 24 horas, entonces el cambio se guarda y se audita.
- **CA-04:** Dado un curso con 10 sesiones y 2 ausencias, cuando consulto, entonces el porcentaje es 80.0 %.
- **CA-05:** Dado un estudiante sin sesiones, cuando consulto, entonces el porcentaje es 0 % o «sin datos», sin división por cero.

### HU-20 · Gestionar evaluaciones
**Como** profesor **quiero** crear evaluaciones con peso y fecha **para** estructurar la nota del curso.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 5 · **Riesgo:** Medio · **RF:** RF-34
- **CA-01:** Dado un curso, cuando creo una evaluación con peso 20 %, entonces aparece en el plan evaluativo.
- **CA-02:** Dado que la suma de pesos supera 100 %, cuando guardo, entonces se rechaza y se muestra el total actual.
- **CA-03:** Dado un curso sin evaluaciones, cuando abro el plan, entonces puedo crear la primera.

### HU-21 · Registrar calificaciones
**Como** profesor **quiero** calificar de 1.0 a 5.0 **para** que el estudiante conozca su desempeño.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 5 · **Riesgo:** Medio · **RF:** RF-35
- **CA-01:** Dado un estudiante matriculado y una evaluación, cuando ingreso 4.5, entonces la nota se guarda y se audita.
- **CA-02:** Dado una nota de 6.0 o 0.5, cuando intento guardar, entonces se rechaza con el rango permitido.
- **CA-03:** Dado una nota existente, cuando la actualizo, entonces se conserva el historial en auditoría.
- **CA-04:** Dado un estudiante RETIRADO, cuando intento calificarlo, entonces se rechaza.

### HU-22 · Consultar mi progreso académico
**Como** estudiante **quiero** ver mi promedio y mi avance **para** saber cómo voy y qué me falta.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 5 · **Riesgo:** Medio · **RF:** RF-36
- **CA-01:** Dado notas 4.0 (30 %), 3.5 (20 %) y una evaluación de 50 % sin calificar, cuando consulto, entonces el avance es 50 % y el promedio es 3.8.
- **CA-02:** Dado un curso con todas las notas, cuando consulto, entonces el avance es 100 %.
- **CA-03:** Dado un promedio menor a 3.0, cuando consulto, entonces veo una advertencia visible.

### HU-23 · Recibir alertas automáticas
**Como** estudiante o profesor **quiero** que el sistema me avise de evaluaciones próximas, entregas y riesgos **para** actuar a tiempo.

- **Prioridad:** Must · **Puntos:** 8 · **Sprint:** 5 · **Riesgo:** Alto · **RF:** RF-37, RF-38, RF-39
- **CA-01:** Dado una evaluación a 5 días, cuando el motor se ejecuta, entonces el estudiante recibe una alerta única.
- **CA-02:** Dado una evaluación vencida sin calificar, cuando el motor se ejecuta, entonces el profesor recibe la alerta.
- **CA-03:** Dado un promedio de 2.8, cuando se calcula, entonces se genera alerta de bajo rendimiento.
- **CA-04:** Dado una cuota vencida, cuando se ejecuta el motor, entonces se alerta al estudiante y al administrador.
- **CA-05:** Dado una alerta ya generada hoy, cuando el motor vuelve a correr, entonces no se duplica.

### HU-24 · Centro de notificaciones
**Como** usuario **quiero** ver mis alertas y marcarlas como leídas **para** gestionar mis pendientes.

- **Prioridad:** Should · **Puntos:** 3 · **Sprint:** 5 · **Riesgo:** Bajo · **RF:** RF-39
- **CA-01:** Dado un usuario con alertas, cuando abre el centro, entonces ve las no leídas primero y con su fecha.
- **CA-02:** Dado una alerta, cuando la marco como leída, entonces el contador del menú disminuye.
- **CA-03:** Dado un usuario sin alertas, cuando abre el centro, entonces ve un estado vacío.

---

## Épica E6 — Dashboard, reportes y configuración

### HU-25 · Dashboard del administrador
**Como** administrador **quiero** ver las métricas clave al entrar **para** detectar situaciones que requieren acción.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 6 · **Riesgo:** Medio · **RF:** RF-40
- **CA-01:** Dado un administrador, cuando entra, entonces ve estudiantes activos, profesores, cursos en curso, ingresos del mes, cartera y % de asistencia.
- **CA-02:** Dado una métrica, cuando pulso «ver detalle», entonces navego al reporte filtrado.
- **CA-03:** Dado una base sin datos, cuando entro, entonces las tarjetas muestran 0 y no errores.

### HU-26 · Dashboard del profesor
**Como** profesor **quiero** ver mis cursos y pendientes **para** priorizar mi trabajo.

- **Prioridad:** Must · **Puntos:** 3 · **Sprint:** 6 · **Riesgo:** Bajo · **RF:** RF-40
- **CA-01:** Dado un profesor, cuando entra, entonces ve sus cursos, sesiones próximas, estudiantes a cargo y evaluaciones sin calificar.
- **CA-02:** Dado un curso ajeno, cuando intento abrirlo por URL, entonces se rechaza.

### HU-27 · Dashboard del estudiante
**Como** estudiante **quiero** ver mi progreso, asistencia, saldo y próximas evaluaciones **para** saber qué hacer.

- **Prioridad:** Must · **Puntos:** 5 · **Sprint:** 6 · **Riesgo:** Medio · **RF:** RF-40
- **CA-01:** Dado un estudiante, cuando entra, entonces ve curso, promedio, avance, asistencia, saldo y próximas evaluaciones.
- **CA-02:** Dado una alerta de pago, cuando entra, entonces la ve en su dashboard.

### HU-28 · Reportes y exportación CSV
**Como** administrador **quiero** filtrar y exportar reportes **para** analizarlos y presentarlos.

- **Prioridad:** Should · **Puntos:** 5 · **Sprint:** 6 · **Riesgo:** Medio · **RF:** RF-41, RF-42, RF-43, RF-45
- **CA-01:** Dado un rango y un nivel, cuando genero el reporte de matrículas, entonces los totales coinciden con la base.
- **CA-02:** Dado un reporte en pantalla, cuando pulso «Exportar CSV», entonces descargo un archivo UTF-8 con encabezados.
- **CA-03:** Dado un profesor, cuando genera reporte, entonces solo incluye sus cursos.

### HU-29 · Filtrar auditoría
**Como** administrador **quiero** filtrar la auditoría **para** investigar un incidente.

- **Prioridad:** Should · **Puntos:** 3 · **Sprint:** 6 · **Riesgo:** Bajo · **RF:** RF-44
- **CA-01:** Dado un usuario y un rango de fechas, cuando filtro, entonces veo solo sus acciones.
- **CA-02:** Dado un módulo y una acción, cuando combino, entonces el resultado es consistente.

### HU-30 · Configurar la academia
**Como** administrador **quiero** ajustar datos de la academia y umbrales de alerta **para** adaptar el sistema sin cambiar código.

- **Prioridad:** Could · **Puntos:** 3 · **Sprint:** 6 · **Riesgo:** Bajo · **RF:** RF-46
- **CA-01:** Dado un cambio de nombre o teléfono, cuando guardo, entonces se refleja en comprobantes y vistas.
- **CA-02:** Dado un umbral de asistencia de 80 %, cuando lo cambio a 75 %, entonces las alertas usan el nuevo valor.

---

## 3.2 Definition of Ready (DoR)

Una historia está lista cuando: tiene RF asociado, criterios de aceptación, prototipo o diseño de pantalla, datos de prueba y dependencias identificadas.

## 3.3 Definition of Done (DoD)

Una historia está terminada cuando: cumple sus criterios, pasó pruebas manuales y automatizadas, no tiene SQL concatenado, valida CSRF y permisos, está comentada, actualiza la trazabilidad y fue demostrada en la sustentación.
