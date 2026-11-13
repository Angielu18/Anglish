package com.anglish.app.data.local.seed

import com.anglish.app.data.local.dao.AsistenciaDao
import com.anglish.app.data.local.dao.CursoDao
import com.anglish.app.data.local.dao.EvaluacionDao
import com.anglish.app.data.local.dao.MatriculaDao
import com.anglish.app.data.local.dao.UsuarioDao
import com.anglish.app.data.local.entity.AsistenciaEntity
import com.anglish.app.data.local.entity.CursoEntity
import com.anglish.app.data.local.entity.EvaluacionEntity
import com.anglish.app.data.local.entity.MatriculaEntity
import com.anglish.app.data.local.entity.UsuarioEntity
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.model.NivelIngles
import com.anglish.app.model.Rol
import com.anglish.app.model.TipoEvaluacion
import java.time.Duration
import java.time.Instant

/**
 * Datos de demostración para el rol Estudiante.
 *
 * Se insertan una sola vez (el repositorio verifica que la tabla usuarios esté
 * vacía). Las fechas de las evaluaciones son relativas al momento de la
 * instalación para que los indicadores de urgencia siempre se puedan demostrar:
 *
 *  - 6 horas   -> rojo (≤ 24 h)
 *  - 40 horas  -> amarillo (≤ 48 h)
 *  - 6 días    -> verde (programada)
 *  - ayer      -> vencida
 *
 * Los valores de progreso (45 %) y asistencia (60 %) coinciden con el caso de
 * Sara Gómez usado en la base de datos MySQL del proyecto web.
 */
class DemoDataSeeder(
    private val usuarioDao: UsuarioDao,
    private val cursoDao: CursoDao,
    private val matriculaDao: MatriculaDao,
    private val asistenciaDao: AsistenciaDao,
    private val evaluacionDao: EvaluacionDao
) {

    suspend fun seed() {
        val ahora = Instant.now()

        usuarioDao.upsert(
            UsuarioEntity(
                id = ESTUDIANTE_ID,
                nombres = "Sara",
                apellidos = "Gómez",
                email = "sara.gomez@anglish.edu.co",
                rol = Rol.ESTUDIANTE,
                nivelActual = NivelIngles.A1
            )
        )

        usuarioDao.upsert(
            UsuarioEntity(
                id = PROFESOR_ID,
                nombres = "Laura",
                apellidos = "Méndez",
                email = "laura.mendez@anglish.edu.co",
                rol = Rol.PROFESOR,
                nivelActual = NivelIngles.B2
            )
        )

        usuarioDao.upsert(
            UsuarioEntity(
                id = ADMIN_ID,
                nombres = "Angie Valentina",
                apellidos = "Sotelo Ramos",
                email = "admin@anglish.edu.co",
                rol = Rol.ADMINISTRADOR,
                nivelActual = NivelIngles.C1
            )
        )

        cursoDao.upsert(
            CursoEntity(
                id = CURSO_ID,
                codigo = "ING-A1-01",
                nombre = "Inglés A1 — Fundamentos",
                nivel = NivelIngles.A1,
                profesor = "Laura Méndez",
                aula = "Aula 201",
                horario = "Lun/Mié · 07:00 - 09:00"
            )
        )

        cursoDao.upsert(
            CursoEntity(
                id = CURSO_B1_ID,
                codigo = "ING-B1-02",
                nombre = "Inglés B1 — Conversación",
                nivel = NivelIngles.B1,
                profesor = "Laura Méndez",
                aula = "Aula 104",
                horario = "Mar/Jue · 18:00 - 20:00"
            )
        )

        matriculaDao.upsert(
            MatriculaEntity(
                id = MATRICULA_ID,
                estudianteId = ESTUDIANTE_ID,
                cursoId = CURSO_ID,
                activa = true,
                progresoPorcentaje = 45.0,
                promedio = 2.53
            )
        )

        asistenciaDao.upsertAll(
            listOf(
                AsistenciaEntity(
                    id = 1,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 1),
                    estado = EstadoAsistencia.PRESENTE,
                    tema = "Present simple: routines"
                ),
                AsistenciaEntity(
                    id = 2,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 3),
                    estado = EstadoAsistencia.AUSENTE,
                    tema = "Listening: at the airport"
                ),
                AsistenciaEntity(
                    id = 3,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 5),
                    estado = EstadoAsistencia.PRESENTE,
                    tema = "Vocabulary: family and friends"
                ),
                AsistenciaEntity(
                    id = 4,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 7),
                    estado = EstadoAsistencia.AUSENTE,
                    tema = "Grammar: there is / there are"
                ),
                AsistenciaEntity(
                    id = 5,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 9),
                    estado = EstadoAsistencia.TARDE,
                    tema = "Introductions and greetings"
                ),
                AsistenciaEntity(
                    id = 6,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 12),
                    estado = EstadoAsistencia.EXCUSA,
                    tema = "Numbers, alphabet and spelling"
                ),
                AsistenciaEntity(
                    id = 7,
                    matriculaId = MATRICULA_ID,
                    fechaMillis = diasAtras(ahora, 14),
                    estado = EstadoAsistencia.PRESENTE,
                    tema = "Induction and placement diagnostics"
                )
            )
        )

        evaluacionDao.upsertAll(
            listOf(
                EvaluacionEntity(
                    id = 1,
                    estudianteId = ESTUDIANTE_ID,
                    cursoId = CURSO_ID,
                    titulo = "Parcial — Unidades 1 y 2",
                    tipo = TipoEvaluacion.PARCIAL,
                    fechaEntregaMillis = horasDesdeAhora(ahora, 6),
                    porcentaje = 25.0
                ),
                EvaluacionEntity(
                    id = 2,
                    estudianteId = ESTUDIANTE_ID,
                    cursoId = CURSO_ID,
                    titulo = "Taller de writing: My daily routine",
                    tipo = TipoEvaluacion.TALLER,
                    fechaEntregaMillis = horasDesdeAhora(ahora, 40),
                    porcentaje = 15.0
                ),
                EvaluacionEntity(
                    id = 3,
                    estudianteId = ESTUDIANTE_ID,
                    cursoId = CURSO_ID,
                    titulo = "Proyecto final: My city",
                    tipo = TipoEvaluacion.PROYECTO,
                    fechaEntregaMillis = horasDesdeAhora(ahora, 24 * 6),
                    porcentaje = 30.0
                ),
                EvaluacionEntity(
                    id = 4,
                    estudianteId = ESTUDIANTE_ID,
                    cursoId = CURSO_ID,
                    titulo = "Quiz de vocabulario",
                    tipo = TipoEvaluacion.QUIZ,
                    fechaEntregaMillis = horasDesdeAhora(ahora, -48),
                    porcentaje = 10.0
                ),
                EvaluacionEntity(
                    id = 5,
                    estudianteId = ESTUDIANTE_ID,
                    cursoId = CURSO_ID,
                    titulo = "Diagnóstico inicial de gramática",
                    tipo = TipoEvaluacion.QUIZ,
                    fechaEntregaMillis = diasAtras(ahora, 14),
                    porcentaje = 10.0,
                    nota = 4.2,
                    completada = true,
                    leida = true
                ),
                EvaluacionEntity(
                    id = 6,
                    estudianteId = ESTUDIANTE_ID,
                    cursoId = CURSO_ID,
                    titulo = "Taller de Listening 1",
                    tipo = TipoEvaluacion.TALLER,
                    fechaEntregaMillis = diasAtras(ahora, 8),
                    porcentaje = 10.0,
                    nota = 3.5,
                    completada = true,
                    leida = true
                )
            )
        )
    }

    private fun diasAtras(ahora: Instant, dias: Long): Long =
        ahora.minus(Duration.ofDays(dias)).toEpochMilli()

    private fun horasDesdeAhora(ahora: Instant, horas: Long): Long =
        ahora.plus(Duration.ofHours(horas)).toEpochMilli()

    private companion object {
        const val ESTUDIANTE_ID = 1L
        const val PROFESOR_ID = 2L
        const val ADMIN_ID = 3L
        const val CURSO_ID = 1L
        const val CURSO_B1_ID = 2L
        const val MATRICULA_ID = 1L
    }
}
