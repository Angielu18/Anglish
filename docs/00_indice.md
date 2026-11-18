
# Anglish — Documentación del proyecto

**Sistema:** Anglish — Academia de inglés
**Desarrolladora:** Angie Valentina Sotelo Ramos
**Programa:** Análisis y Desarrollo de Software (ADSO) — SENA
**Stack:** PHP 8.0+ nativo (POO), MySQL 8.0, PDO, MVC artesanal, Bootstrap 5, JavaScript nativo. App móvil: Kotlin, MVVM, Room, Retrofit, Material 3.
**Moneda:** COP · **Escala de calificación:** 1.0 a 5.0 (aprueba con 3.0) · **Zona horaria:** America/Bogota.

---

## 1. Estado de las fases

| Fase | Entregable | Documento | Estado |
|---|---|---|---|
| 1 | Requisitos, historias de usuario, casos de uso y trazabilidad | `docs/01` a `docs/06` | ✅ Entregada |
| 2 | Modelo EER, DDL MySQL en 3FN, datos de prueba y consultas | `docs/07`, `database/*` | ✅ Entregada |
| 3 | Estructura MVC, PDO, router, login y dashboard web | `index.php`, `app/*`, `public/*` | ⏳ En desarrollo |
| 4 | Módulos académicos, matrículas, pagos y asistencia | — | ⏳ Pendiente |
| 5 | Reportes, alertas, auditoría y pruebas integrales | — | ⏳ Pendiente |
| A1 | App Android: estructura, dominio, Room/Retrofit, ViewModel y Dashboard del Estudiante | `android/*`, `docs/10` | ✅ Entregada |

> El avance web se encuentra en la fase 3. La app Android se desarrolló como complemento móvil sin modificar el proyecto PHP/MySQL; su primer incremento ya compila, pasa lint y pruebas unitarias.

## 2. Índice de documentos

1. [`01_vision_alcance.md`](01_vision_alcance.md) — problema, objetivos, alcance, interesados, supuestos, restricciones y normativa.
2. [`02_requisitos.md`](02_requisitos.md) — 46 requisitos funcionales, 22 no funcionales y 20 reglas de negocio.
3. [`03_historias_usuario.md`](03_historias_usuario.md) — 30 historias de usuario con criterios de aceptación (Gherkin), puntos y sprint.
4. [`04_casos_uso.md`](04_casos_uso.md) — actores, diagrama, inventario y 6 casos de uso detallados.
5. [`05_trazabilidad.md`](05_trazabilidad.md) — matriz RF ↔ HU ↔ CU ↔ tabla ↔ endpoint ↔ prueba.
6. [`06_backlog.md`](06_backlog.md) — épicas, sprints, Definition of Ready/Done y CSV para Jira.
7. [`07_modelo_datos.md`](07_modelo_datos.md) — modelo EER, normalización 3FN, diccionario de datos y vistas.
8. `08_manual_instalacion.md` — puesta en marcha en XAMPP (pendiente de redacción).
9. `09_manual_usuario.md` — uso por rol y recorrido funcional (pendiente de redacción).
10. [`10_android_arquitectura.md`](10_android_arquitectura.md) — arquitectura móvil, Room/Retrofit, flujo offline-first y guía de sustentación.

## 3. Artefactos ejecutables

| Ruta | Contenido |
|---|---|
| `database/schema.sql` | DDL completo: 20 tablas, llaves foráneas, CHECK, índices y 7 vistas. |
| `database/datos_prueba.sql` | Semilla coherente: 3 roles, 23 permisos, 4 profesores, 12 estudiantes, 6 cursos, pagos, asistencia, notas y alertas. |
| `database/consultas.sql` | 18 consultas de dashboard, cartera, asistencia y progreso. |
| `android/` | App Android Kotlin con Dashboard del Estudiante, Room, Retrofit y pruebas unitarias. |
| `android/app/build/outputs/apk/debug/app-debug.apk` | APK de depuración generado por Gradle. |
| `index.php` | Front Controller único (pendiente de la fase 3). |
| `tests/run_tests.php` | Suite de pruebas sin dependencias externas (pendiente de la fase 3). |

## 4. Supuestos acordados

- Cada curso pertenece a **un** nivel y tiene **un** profesor titular (la asignación de varios profesores por curso queda fuera del alcance).
- La matrícula genera cuotas mensuales iguales; el valor se distribuye en `numero_cuotas` cuotas.
- La asistencia se registra por **sesión de clase** y por **matrícula** (no por usuario).
- Una nota solo se registra si la evaluación existe y el estudiante tiene matrícula ACTIVA.
- El porcentaje de asistencia considera `PRESENTE`, `TARDE` y `EXCUSA` como asistencia efectiva; solo `AUSENTE` resta.
- El envío real de correos queda simulado con registro en `auditoria`/log; el motor de alertas es interno.
- El proyecto se ejecuta en `http://localhost/Anglish` (XAMPP, Apache en el puerto 80).

## 5. Credenciales de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@anglish.edu.co` | `Admin123*` |
| Profesor | `laura.mendez@anglish.edu.co` | `Profe123*` |
| Estudiante | `sara.gomez@anglish.edu.co` | `Estudiante123*` |

> Cambiar estas credenciales antes de cualquier despliegue real.
