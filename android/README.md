# Anglish Android

Aplicación móvil nativa del sistema **Anglish — Academia de inglés**, desarrollada en Kotlin con arquitectura MVVM. Este primer incremento entrega la base técnica y el **Dashboard del rol Estudiante** con progreso académico, resumen de asistencia y centro de alertas.

> Proyecto académico ADSO — SENA. La app consume el mismo modelo de negocio del proyecto web PHP/MySQL, pero funciona de forma independiente y offline-first mientras el backend expone la API REST.

---

## 1. Alcance de esta entrega

| Entregable | Estado |
|---|---|
| Estructura Android Studio + Gradle + ViewBinding | ✅ |
| Modelos de dominio (`model/`) | ✅ |
| Capa local Room/SQLite (entidades, DAO, base de datos, semilla) | ✅ |
| Capa remota Retrofit + Gson + OkHttp | ✅ |
| Sesión cifrada con EncryptedSharedPreferences | ✅ |
| Repositorio offline-first | ✅ |
| ViewModel con estados Loading / Success / Error | ✅ |
| Dashboard del Estudiante | ✅ |
| Centro de alertas con urgencia por colores | ✅ |
| Pruebas unitarias de urgencia | ✅ |
| Pantalla de Login | ⏳ siguiente incremento |
| Módulos Asistencia y Alertas como pantallas independientes | ⏳ siguiente incremento |
| Dashboard del Profesor | ⏳ siguiente incremento |

El Dashboard ya incluye la asistencia reciente y las alertas; en los siguientes incrementos se convertirán en pantallas completas reutilizando los mismos repositorios y DAO.

---

## 2. Stack técnico

| Componente | Versión / decisión |
|---|---|
| Lenguaje | Kotlin (Kotlin integrado de AGP 9.5.1) |
| Android Gradle Plugin | 9.5.1 |
| Gradle | 9.6.0 (wrapper incluido) |
| compileSdk / targetSdk | 37 |
| minSdk | 26 (permite `java.time` sin desugaring) |
| Java/JVM target | 17 |
| Interfaz | XML + ViewBinding + Material 3 |
| Arquitectura | MVVM + separación por capas |
| Persistencia local | Room 2.8.5 sobre SQLite |
| Procesador de anotaciones | KSP 2.3.13 |
| Red | Retrofit 2.11.0 + Gson + OkHttp 4.12.0 |
| Asincronía | Corrutinas + Flow / StateFlow |
| Sesión | EncryptedSharedPreferences 1.1.0 |
| Navegación | Navigation Component 2.8.5, una sola Activity |

---

## 3. Requisitos para abrir el proyecto

1. **Android Studio** reciente (probado con la versión 2026.2).
2. **Android SDK Platform 37** y **Build Tools 36.0.0** instalados.
3. **JDK 17 o superior**; Android Studio incluye su propio JBR.
4. Conexión a Internet la primera vez para descargar Gradle y dependencias.

El archivo `local.properties` se genera automáticamente en Android Studio y no se versiona. Si se compila por consola, debe contener:

```properties
sdk.dir=C:/Users/<usuario>/AppData/Local/Android/Sdk
```

### Compilar y probar

```powershell
cd android
.\gradlew.bat :app:assembleDebug
.\gradlew.bat :app:testDebugUnitTest
```

El APK de depuración queda en:

```text
android/app/build/outputs/apk/debug/app-debug.apk
```

### Ejecutar

1. Abrir la carpeta `android/` en Android Studio.
2. Esperar la sincronización de Gradle.
3. Seleccionar un emulador o dispositivo con Android 8.0 (API 26) o superior.
4. Ejecutar la configuración `app`.

La primera vez se crea una sesión de demostración y se insertan datos locales; no se necesita backend para ver el Dashboard.

---

## 4. Usuario de demostración

| Campo | Valor |
|---|---|
| Nombre | Sara Gómez |
| Correo | `sara.gomez@anglish.edu.co` |
| Rol | Estudiante |
| Nivel | A1 |
| Curso | Inglés A1 — Fundamentos |
| Profesora | Laura Méndez |
| Progreso | 45 % |
| Promedio | 2.53 / 5.0 |
| Asistencia | 60.0 % |

La sesión se guarda cifrada y los datos se siembran en Room solo si la tabla `usuarios` está vacía. Para reiniciar la demostración: **Ajustes de Android → Apps → Anglish → Almacenamiento → Borrar datos**.

Las fechas de las evaluaciones se generan relativas al momento de la instalación para poder demostrar siempre los cuatro niveles de urgencia.

---

## 5. Arquitectura

```text
┌─────────────────────────────────────────────────────────────┐
│                         UI (XML)                            │
│  MainActivity · DashboardFragment · RecyclerView adapters   │
└──────────────────────────┬──────────────────────────────────┘
                           │ eventos / observación de estado
┌──────────────────────────▼──────────────────────────────────┐
│                     ViewModel (MVVM)                         │
│  DashboardViewModel · DashboardUiState · DashboardEvent     │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                    DashboardRepository                       │
│  combina flujos · sincroniza · resuelve offline-first        │
└───────────────┬──────────────────────────────┬───────────────┘
                │                              │
┌───────────────▼──────────────┐  ┌────────────▼──────────────┐
│ Room / SQLite                │  │ Retrofit + OkHttp + Gson   │
│ fuente única de verdad local │  │ API REST /api/v1           │
└──────────────────────────────┘  └────────────────────────────┘
```

### Regla de oro

**Room es la única fuente de verdad para la interfaz.** Retrofit nunca actualiza directamente un `TextView`; su respuesta se mapea a entidades y se escribe en Room. La UI observa `Flow` de Room y se redibuja automáticamente.

### Flujo de una actualización

1. El usuario desliza el `SwipeRefreshLayout`.
2. `DashboardFragment` llama `viewModel.refrescar()`.
3. El ViewModel ejecuta `repository.sincronizar()` en `viewModelScope`.
4. El repositorio consulta `GET /estudiantes/{id}/dashboard`.
5. La respuesta se mapea a entidades y se guarda en una **transacción** de Room.
6. Room emite nuevos datos.
7. El ViewModel transforma y emite `DashboardUiState.Success`.
8. El Fragment renderiza y oculta el indicador de carga.

Si el paso 4 falla, no se borra nada: se conserva la caché local y se muestra el aviso **Modo offline**.

---

## 6. Estructura de paquetes

```text
com.anglish.app
├── AnglishApplication.kt          # Application y punto de composición
├── MainActivity.kt                # Activity única + edge-to-edge
├── di/
│   └── AppContainer.kt            # Inyección manual de dependencias
├── model/                         # Dominio puro, sin Android
│   ├── Usuario.kt
│   ├── Curso.kt
│   ├── Asistencia.kt
│   ├── EvaluacionAlerta.kt
│   ├── DashboardEstudiante.kt
│   └── enums: Rol, NivelIngles, EstadoAsistencia, TipoEvaluacion, Urgencia
├── data/
│   ├── local/
│   │   ├── entity/                # Tablas Room
│   │   ├── dao/                   # Consultas y agregados SQL
│   │   ├── model/                 # Proyecciones de JOIN
│   │   ├── Converters.kt
│   │   ├── AnglishDatabase.kt
│   │   └── seed/DemoDataSeeder.kt
│   ├── remote/
│   │   ├── dto/                   # Contrato JSON
│   │   ├── AnglishApiService.kt
│   │   ├── AuthInterceptor.kt
│   │   ├── DateParser.kt
│   │   └── RetrofitClient.kt
│   ├── mapper/                    # Entity ↔ Domain y DTO → Entity
│   ├── repository/DashboardRepository.kt
│   └── session/SessionManager.kt
├── viewmodel/
│   ├── DashboardViewModel.kt
│   └── DashboardUiState.kt
└── ui/
    ├── common/FechaUi.kt
    └── dashboard/
        ├── DashboardFragment.kt
        ├── EvaluacionAdapter.kt
        └── AsistenciaAdapter.kt
```

---

## 7. Base de datos local (Room)

| Tabla | Propósito | Llave foránea |
|---|---|---|
| `usuarios` | Caché del usuario autenticado | — |
| `cursos` | Curso y profesor | — |
| `matriculas` | Curso activo, progreso y promedio | `estudianteId`, `cursoId` |
| `asistencias` | Sesiones y estado | `matriculaId` |
| `evaluaciones` | Entregas, nota y estado de lectura | `estudianteId`, `cursoId` |

### Consultas destacadas

- **Curso activo:** `cursos JOIN matriculas WHERE activa = 1`.
- **Asistencia reciente:** últimas 5 sesiones ordenadas por fecha.
- **Resumen de asistencia:** agregado en SQL con `CASE`, `SUM`, `NULLIF` y `ROUND`; evita traer cientos de filas a memoria.
- **Alertas:** evaluaciones no completadas, ordenadas por fecha límite.

El porcentaje de asistencia sigue la regla del proyecto:

```text
(presentes + tardes + excusas) / total * 100
```

Una inasistencia es el único estado que resta.

---

## 8. Reglas de urgencia

`EvaluacionAlerta.urgencia(ahora)` calcula el nivel en el dominio, no en el adaptador. Así se puede probar con JUnit y el color es solo una consecuencia visual.

| Condición | Enum | Color | Etiqueta |
|---|---|---|---|
| Fecha límite ya pasó | `VENCIDA` | Rojo oscuro | Vencida |
| Faltan menos de 24 h | `HOY` | Rojo | ≤ 24 h |
| Faltan entre 24 y 48 h | `PROXIMA` | Amarillo | ≤ 48 h |
| Faltan más de 48 h | `NORMAL` | Verde | Programada |

Al tocar una alerta se marca como leída en Room (`UPDATE evaluaciones SET leida = 1`). El cambio se refleja de inmediato en la lista y se muestra un `Snackbar`.

---

## 9. Contrato REST esperado

La URL base por defecto es:

```text
http://10.0.2.2:8080/api/v1/
```

`10.0.2.2` es el localhost del computador visto desde el emulador. Se puede cambiar sin editar código:

```powershell
.\gradlew.bat :app:assembleDebug -PANGLISH_API_URL="http://10.0.2.2/Anglish/api/v1/"
```

### `GET /estudiantes/{id}/dashboard`

```json
{
  "estudiante": {
    "id": 1,
    "nombres": "Sara",
    "apellidos": "Gómez",
    "email": "sara.gomez@anglish.edu.co",
    "rol": "ESTUDIANTE",
    "nivel": "A1"
  },
  "curso": {
    "id": 1,
    "codigo": "ING-A1-01",
    "nombre": "Inglés A1 — Fundamentos",
    "nivel": "A1",
    "profesor": "Laura Méndez",
    "aula": "Aula 201",
    "horario": "Lun/Mié · 07:00 - 09:00"
  },
  "matricula": {
    "id": 1,
    "curso_id": 1,
    "progreso_porcentaje": 45,
    "promedio": 2.53
  },
  "evaluaciones": [
    {
      "id": 1,
      "curso_id": 1,
      "titulo": "Parcial — Unidades 1 y 2",
      "tipo": "PARCIAL",
      "fecha_entrega": "2026-11-08 18:00:00",
      "porcentaje": 25,
      "nota": null,
      "leida": false,
      "completada": false
    }
  ],
  "asistencias": [
    {
      "id": 1,
      "matricula_id": 1,
      "fecha": "2026-11-07 07:00:00",
      "estado": "PRESENTE",
      "tema": "Present simple: routines"
    }
  ]
}
```

El parser acepta fechas ISO-8601 (`2026-11-08T18:00:00Z`), con offset, o formato MySQL (`yyyy-MM-dd HH:mm:ss`).

### `POST /auth/login` (previsto)

```json
{
  "email": "sara.gomez@anglish.edu.co",
  "password": "Estudiante123*"
}
```

La app aún no muestra el formulario de Login; la sesión de demostración se crea automáticamente. El DTO y el endpoint ya están preparados para el siguiente incremento.

---

## 10. Seguridad y decisiones de diseño

- **Token en almacenamiento cifrado:** `EncryptedSharedPreferences` con clave maestra del Android Keystore.
- **Rol de menor privilegio:** cualquier valor de rol inválido se interpreta como `ESTUDIANTE`.
- **Validación de datos remotos:** si falta un ID o una fecha obligatoria, el registro se omite en lugar de guardar datos corruptos.
- **`@Upsert` en lugar de `REPLACE`:** evita que una actualización de usuario dispare `ON DELETE CASCADE` y borre matrículas, asistencias y evaluaciones.
- **Transacciones:** la sincronización borra e inserta el caché dentro de `database.withTransaction`; si falla, Room revierte todo.
- **HTTP solo en desarrollo:** `network_security_config.xml` permite texto plano únicamente para `10.0.2.2`, `localhost` y `127.0.0.1`.
- **Logging de red solo en debug** y con `Authorization` redactado.
- **Migraciones:** al ser una caché de esta primera entrega, se usa reconstrucción destructiva si cambia el esquema. Antes de producción se deben escribir migraciones reales.

---

## 11. Pruebas

```powershell
.\gradlew.bat :app:testDebugUnitTest
```

La prueba `EvaluacionAlertaTest` valida:

- evaluación vencida → `VENCIDA`;
- menos de 24 horas → `HOY`;
- entre 24 y 48 horas → `PROXIMA`;
- más de 48 horas → `NORMAL`.

Resultado obtenido en la compilación de esta entrega: **BUILD SUCCESSFUL** y pruebas unitarias aprobadas.

---

## 12. Limitaciones conocidas y siguientes pasos

1. Implementar `LoginFragment` y navegación por rol (Estudiante / Profesor).
2. Implementar las pantallas completas de Asistencia y Alertas, reutilizando `AsistenciaDao` y `EvaluacionDao`.
3. Exponer `/api/v1` desde el backend PHP con tokens y validación de permisos.
4. Agregar pruebas instrumentadas del Dashboard y de la base Room.
5. Incorporar WorkManager para sincronización periódica en segundo plano.
6. Escribir migraciones Room cuando el esquema deje de ser solo caché.

---

## 13. Relación con el proyecto web

- Misma escala de calificación: **1.0 a 5.0**, aprueba con **3.0**.
- Misma zona horaria: **America/Bogota**.
- Misma regla de asistencia y mismos estados (`PRESENTE`, `TARDE`, `AUSENTE`, `EXCUSA`).
- Mismos niveles A1–C1.
- El caso de Sara Gómez replica el escenario de prueba de MySQL: 45 % de progreso, promedio 2.53 y 60 % de asistencia.
