# 1. Visión y alcance del sistema Anglish

## 1.1 Identificación

| Campo | Valor |
|---|---|
| Nombre del sistema | Anglish — Academia de inglés |
| Tipo | Aplicación web de gestión académica y financiera |
| Desarrolladora | Angie Valentina Sotelo Ramos |
| Programa | Análisis y Desarrollo de Software (ADSO) |
| Metodología | Desarrollo incremental por sprints, con documentación ISO/IEC/IEEE 29148 |
| Versión del documento | 1.0 |

## 1.2 Problema u oportunidad

La academia Anglish gestiona estudiantes, profesores, cursos, matrículas, pagos y asistencia en hojas de cálculo y registros manuales. Esto produce:

- **Pérdida de trazabilidad** entre la matrícula, el pago y el avance académico.
- **Cartera difusa**: no se sabe con certeza quién está al día, pendiente o en mora.
- **Asistencia tardía**: se toma en papel y se digita días después, impidiendo reaccionar a tiempo.
- **Riesgo de deserción**: nadie detecta a tiempo inasistencias reiteradas, notas bajas o evaluaciones pendientes.
- **Reportes costosos**: consolidar ingresos, estudiantes activos e inasistencias toma horas.

Anglish centraliza estos procesos en una aplicación web responsive, con roles diferenciados y una capa de seguimiento académico que convierte los datos operativos en alertas accionables.

## 1.3 Objetivos

### Objetivo general

Desarrollar una aplicación web con arquitectura MVC en PHP 8 nativo y MySQL, que automatice la gestión académica, financiera y de seguimiento de la academia Anglish, garantizando seguridad, trazabilidad y disponibilidad de la información.

### Objetivos específicos

1. Implementar autenticación y control de acceso basado en roles (RBAC) para administrador, profesor y estudiante.
2. Gestionar niveles A1–C1, cursos, horarios, profesores y estudiantes con validación de cupos y solapamientos.
3. Automatizar matrículas, generación de cuotas, registro de pagos y cálculo del estado de cartera.
4. Registrar asistencia por sesión de clase y calcular el porcentaje de asistencia por estudiante.
5. Calcular el progreso académico mediante evaluaciones ponderadas y promedios en escala 1.0–5.0.
6. Generar alertas automáticas de evaluaciones próximas, entregas pendientes, bajo rendimiento y cartera vencida.
7. Proveer dashboards y reportes filtrables para la toma de decisiones y la auditoría.
8. Aplicar buenas prácticas de seguridad: PDO con consultas preparadas, `password_hash()`, CSRF, escape de salida y sesiones seguras.

## 1.4 Alcance funcional

| Módulo | Incluido |
|---|---|
| Autenticación y RBAC | Login, logout, recuperación de contraseña, cambio de clave, roles, permisos, auditoría de accesos. |
| Gestión académica | Niveles A1–C1, cursos, horarios, profesores, agenda, sesiones de clase, estados del curso. |
| Estudiantes y matrículas | CRUD de estudiantes, ficha 360°, matrícula con validación de cupo, generación de cuotas, retiro. |
| Pagos y finanzas | Registro de pagos, aplicación a cuotas, anulación, estado de cuenta, recibos, ingresos y cartera. |
| Asistencia | Registro por sesión en tiempo real, edición controlada, porcentaje por estudiante. |
| Progreso y alertas | Evaluaciones ponderadas, notas 1.0–5.0, promedio, avance, alertas de evaluaciones, pagos y rendimiento. |
| Dashboard y reportes | Métricas por rol, reportes de matrícula, asistencia, ingresos, cartera, exportación CSV, auditoría. |

### Fuera del alcance (versión 1.0)

- Aplicación móvil nativa (la web es responsive).
- Pasarela de pagos en línea; los pagos se registran manualmente por el administrador.
- Videoconferencias o aula virtual integrada.
- Nómina y contabilidad formal de la academia.
- Facturación electrónica DIAN.
- Envío real de correo/SMS; las notificaciones son internas y el correo se simula.
- Multi-sede y multi-idioma.

## 1.5 Interesados (stakeholders)

| Interesado | Interés principal | Influencia |
|---|---|---|
| Dueña/coordinadora de Anglish | Visibilidad de ingresos, cartera y ocupación de cursos. | Alta |
| Administrador del sistema | Operar usuarios, matrículas, pagos y reportes. | Alta |
| Profesor | Registrar asistencia y notas de sus cursos sin fricción. | Media |
| Estudiante | Consultar su progreso, asistencia, pagos y alertas. | Media |
| Acudiente | Conocer el estado académico y financiero del estudiante. | Baja |
| Desarrolladora | Entregar un producto mantenible y sustentable. | Alta |
| Instructor ADSO | Evaluar cumplimiento técnico y documental. | Alta |

## 1.6 Supuestos

1. La academia opera en una sola sede y en zona horaria `America/Bogota`.
2. Cada estudiante tiene un usuario asociado; el acudiente no inicia sesión en la versión 1.0.
3. Cada curso tiene un profesor titular y un horario semanal fijo.
4. Las cuotas son mensuales e iguales; el administrador define número de cuotas al matricular.
5. Los pagos se registran el mismo día en que se reciben y en pesos colombianos (COP).
6. El servidor dispone de PHP 8.0+, MySQL 8.0 y Apache (XAMPP) con `mod_rewrite`.
7. El volumen esperado es de hasta 1.000 estudiantes activos y 50.000 registros de asistencia por año.

## 1.7 Restricciones

| Tipo | Restricción |
|---|---|
| Técnica | PHP 8+ nativo con POO; prohibido Laravel, Symfony, CodeIgniter o cualquier framework PHP. |
| Técnica | Persistencia exclusiva con PDO, `prepare()`/`execute()` y manejo de excepciones. |
| Técnica | MySQL como SGBD; modelo relacional normalizado hasta 3FN. |
| Técnica | MVC artesanal con Front Controller; sin ORM. |
| Interfaz | HTML5 semántico, CSS3, JavaScript nativo y Bootstrap 5; responsive desde 360 px. |
| Seguridad | `password_hash()`/`password_verify()`, CSRF, escape de salida, sesiones seguras. |
| Legal | Ley 1581 de 2012 y Decreto 1377 de 2013 (protección de datos personales en Colombia). |
| Tiempo | Proyecto individual con entregas semanales; alcance priorizado con MoSCoW. |
| Operativa | Despliegue local en XAMPP; sin presupuesto para servicios en la nube. |

## 1.8 Normativa y estándares de referencia

- **ISO/IEC/IEEE 29148:2018** — atributos y calidad de los requisitos (identificador, prioridad, fuente, criterio de aceptación, método de verificación).
- **ISO/IEC 25010:2011** — categorías de calidad para los requisitos no funcionales.
- **Ley 1581 de 2012 y Decreto 1377 de 2013** — tratamiento de datos personales.
- **OWASP Top 10 (2021)** — prevención de inyección SQL, XSS, CSRF, control de acceso quebrado y fallas de autenticación.
- **WCAG 2.1 nivel AA** — accesibilidad de la interfaz.
- **PSR-12 (adaptada)** — estilo de codificación PHP.

## 1.9 Glosario

| Término | Definición |
|---|---|
| Nivel MCER | A1, A2, B1, B2, C1: escala del Marco Común Europeo de Referencia. |
| Matrícula | Vínculo entre un estudiante y un curso; habilita asistencia, notas y cuotas. |
| Cuota | Obligación de pago mensual generada por una matrícula. |
| Cartera | Diferencia entre lo facturado y lo pagado por una matrícula. |
| Sesión de clase | Encuentro programado de un curso en una fecha y hora determinadas. |
| Progreso | Porcentaje del peso evaluativo ya calificado sobre el total del curso. |
| Promedio ponderado | Suma de (nota × peso) dividida entre la suma de pesos calificados. |
| RBAC | Control de acceso basado en roles (*Role-Based Access Control*). |
| CSRF | Falsificación de petición en sitios cruzados. |
| Front Controller | Único punto de entrada que enruta todas las peticiones HTTP. |

## 1.10 Criterios de éxito del producto

1. Un administrador puede matricular un estudiante, generar sus cuotas y registrar un pago sin salir del sistema en menos de 3 minutos.
2. Un profesor registra la asistencia completa de un curso de 20 estudiantes en menos de 2 minutos.
3. El estudiante ve su progreso, asistencia, estado de cuenta y alertas al iniciar sesión.
4. El dashboard administrativo muestra estudiantes activos, ingresos del mes e inasistencias en una sola pantalla.
5. Ninguna consulta SQL se construye por concatenación de entrada del usuario.
6. El 100 % de los casos de prueba críticos (autenticación, matrícula, pago, asistencia, nota) pasa en la sustentación.
