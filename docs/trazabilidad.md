# Matriz de Trazabilidad de Requisitos — Sistema Anglish

**Proyecto:** Anglish — Academia de inglés  
**Programa:** Análisis y Desarrollo de Software (ADSO) — SENA  
**Versión:** 1.0  
**Herramienta de Gestión:** Jira Software (Scrum)  

---

## 1. Matriz de Trazabilidad (Requirements Traceability Matrix)

| Requisito | Historia de Usuario | Módulo | Funcionalidad | Caso de Prueba | Estado en Sprint |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **RF-01** | **HU-01** | Seguridad y Acceso | Autenticación formal por credenciales según rol (sin bypass) | CP-01 | **Done** |
| **RF-02** | **HU-02** | Público / Visitantes | Modo Visitante: Navegación y consulta libre sin registro | CP-02 | **Done** |
| **RF-03** | **HU-03** | Público / Visitantes | Landing Page: Sección "¿Quiénes somos?", objetivos y programas | CP-03 | **Done** |
| **RF-04** | **HU-04** | Estudiantes | Inicio: Dashboard principal con progreso y alertas académicas | CP-04 | **Done** |
| **RF-05** | **HU-05** | Estudiantes | Entregar trabajos: Subir tareas con comentarios y archivos | CP-05 | **In Progress** |
| **RF-06** | **HU-06** | Estudiantes | Ver notas: Visualización de calificaciones y retroalimentación docente | CP-06 | **In Progress** |
| **RF-07** | **HU-07** | Estudiantes | Reportes: Gráficas de rendimiento académico e historial de asistencia | CP-07 | **To Do** |
| **RF-08** | **HU-08** | Profesores | Inicio: Dashboard del docente con métricas de cursos y estudiantes | CP-08 | **Done** |
| **RF-09** | **HU-09** | Profesores | Gestionar tareas: Crear y asignar tareas por nivel o curso | CP-09 | **In Progress** |
| **RF-10** | **HU-10** | Profesores | Revisar trabajos: Calificar entregas y emitir retroalimentación | CP-10 | **Testing** |
| **RF-11** | **HU-11** | Profesores | Reportes: Rendimiento grupal, promedios y alumnos en riesgo | CP-11 | **To Do** |
| **RF-12** | **HU-12** | Administrador | Gestionar entrevistas: Programar, coordinar y registrar resultados | CP-12 | **To Do** |
| **RF-13** | **HU-13** | Administrador | Gestión de acceso: Control de usuarios, roles (Admin, Profe, Estudiante) | CP-13 | **In Progress** |

---

## 2. Definición del Product Backlog en Jira

### Épica 1: Módulo Público y Acceso (ANG-EPIC-1)
* **HU-01:** Como usuario registrado (Admin, Profesor, Estudiante), quiero iniciar sesión formalmente con mi correo y contraseña para acceder de forma segura a mis servicios según mi rol. (Estimación: 3 SP, Prioridad: Alta).
* **HU-02:** Como visitante no registrado, quiero navegar por el sitio web sin iniciar sesión para conocer los servicios y programas de la academia sin restricciones. (Estimación: 2 SP, Prioridad: Media).
* **HU-03:** Como visitante, quiero visualizar la página principal con la sección "¿Quiénes somos?", objetivos pedagógicos y oferta de niveles (A1 a C1) para tomar una decisión de inscripción. (Estimación: 3 SP, Prioridad: Alta).

### Épica 2: Módulo de Estudiantes (ANG-EPIC-2)
* **HU-04:** Como estudiante, quiero ver mi tablero de inicio con alertas académicas y porcentaje de asistencia para mantenerme al día. (Estimación: 5 SP, Prioridad: Alta).
* **HU-05:** Como estudiante, quiero entregar mis tareas asignadas adjuntando información o archivos para que sean evaluadas por mi profesor. (Estimación: 5 SP, Prioridad: Alta).
* **HU-06:** Como estudiante, quiero consultar mis notas y leer la retroalimentación de cada trabajo para mejorar mi aprendizaje. (Estimación: 3 SP, Prioridad: Media).
* **HU-07:** Como estudiante, quiero generar y ver reportes de mi asistencia y notas históricas para monitorear mi rendimiento. (Estimación: 3 SP, Prioridad: Media).

### Épica 3: Módulo de Profesores (ANG-EPIC-3)
* **HU-08:** Como profesor, quiero visualizar en mi dashboard los cursos activos a mi cargo y el total de alumnos inscritos para organizar mi jornada docente. (Estimación: 3 SP, Prioridad: Alta).
* **HU-09:** Como profesor, quiero crear y asignar nuevas tareas especificando título, instrucciones, porcentaje y fecha límite según el curso para evaluar a mis grupos. (Estimación: 5 SP, Prioridad: Alta).
* **HU-10:** Como profesor, quiero revisar las entregas de los estudiantes, calificar de 1.0 a 5.0 y registrar observaciones individuales. (Estimación: 5 SP, Prioridad: Alta).
* **HU-11:** Como profesor, quiero consultar reportes consolidados por grupo para identificar estudiantes que requieran refuerzo. (Estimación: 3 SP, Prioridad: Media).

### Épica 4: Módulo de Administrador (ANG-EPIC-4)
* **HU-12:** Como administrador, quiero programar y gestionar entrevistas de nivelación con nuevos aspirantes para coordinar su ingreso a la academia. (Estimación: 5 SP, Prioridad: Alta).
* **HU-13:** Como administrador, quiero gestionar usuarios, asignar roles y cambiar estados (activo, inactivo, bloqueado) para mantener la seguridad del sistema. (Estimación: 5 SP, Prioridad: Muy Alta).
