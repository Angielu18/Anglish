package com.anglish.app.data.mapper

import com.anglish.app.data.local.model.AsistenciaConCurso
import com.anglish.app.data.local.model.EvaluacionConCurso
import com.anglish.app.data.local.model.ResumenAsistenciaDb
import com.anglish.app.data.local.entity.CursoEntity
import com.anglish.app.data.local.entity.UsuarioEntity
import com.anglish.app.model.Asistencia
import com.anglish.app.model.Curso
import com.anglish.app.model.EvaluacionAlerta
import com.anglish.app.model.ResumenAsistencia
import com.anglish.app.model.Usuario
import java.time.Instant

/**
 * Mapeos Entity (Room) -> Model (dominio).
 *
 * La UI solo conoce los modelos de /model; no ve entidades ni columnas.
 */

fun UsuarioEntity.toDomain(): Usuario = Usuario(
    id = id,
    nombres = nombres,
    apellidos = apellidos,
    email = email,
    rol = rol,
    nivelActual = nivelActual,
    avatarUrl = avatarUrl
)

fun CursoEntity.toDomain(): Curso = Curso(
    id = id,
    codigo = codigo,
    nombre = nombre,
    nivel = nivel,
    profesor = profesor,
    aula = aula,
    horario = horario
)

fun AsistenciaConCurso.toDomain(): Asistencia = Asistencia(
    id = asistencia.id,
    cursoNombre = cursoNombre,
    fecha = Instant.ofEpochMilli(asistencia.fechaMillis),
    estado = asistencia.estado,
    tema = asistencia.tema
)

fun EvaluacionConCurso.toDomain(): EvaluacionAlerta = EvaluacionAlerta(
    id = evaluacion.id,
    cursoNombre = cursoNombre,
    titulo = evaluacion.titulo,
    tipo = evaluacion.tipo,
    fechaEntrega = Instant.ofEpochMilli(evaluacion.fechaEntregaMillis),
    porcentaje = evaluacion.porcentaje,
    nota = evaluacion.nota,
    leida = evaluacion.leida,
    completada = evaluacion.completada
)

fun ResumenAsistenciaDb.toDomain(): ResumenAsistencia = ResumenAsistencia(
    total = total,
    presentes = presentes,
    tardes = tardes,
    ausentes = ausentes,
    excusas = excusas,
    porcentaje = porcentaje
)
