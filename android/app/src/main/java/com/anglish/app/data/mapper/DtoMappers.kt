package com.anglish.app.data.mapper

import com.anglish.app.data.local.entity.AsistenciaEntity
import com.anglish.app.data.local.entity.CursoEntity
import com.anglish.app.data.local.entity.EvaluacionEntity
import com.anglish.app.data.local.entity.MatriculaEntity
import com.anglish.app.data.local.entity.UsuarioEntity
import com.anglish.app.data.remote.DateParser
import com.anglish.app.data.remote.dto.AsistenciaDto
import com.anglish.app.data.remote.dto.CursoDto
import com.anglish.app.data.remote.dto.DashboardDto
import com.anglish.app.data.remote.dto.EstudianteDto
import com.anglish.app.data.remote.dto.EvaluacionDto
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.model.NivelIngles
import com.anglish.app.model.Rol
import com.anglish.app.model.TipoEvaluacion

/**
 * Mapeos DTO (JSON) -> Entity (Room).
 *
 * Si un dato obligatorio falta, se devuelve null y el repositorio lo omite en
 * lugar de guardar registros corruptos.
 */

fun EstudianteDto.toEntity(): UsuarioEntity? {
    val id = id ?: return null
    return UsuarioEntity(
        id = id,
        nombres = nombres?.takeIf { it.isNotBlank() } ?: "Estudiante",
        apellidos = apellidos.orEmpty(),
        email = email?.takeIf { it.isNotBlank() } ?: "sin-correo-$id@anglish.local",
        rol = Rol.from(rol),
        nivelActual = NivelIngles.from(nivel),
        avatarUrl = avatarUrl
    )
}

fun CursoDto.toEntity(): CursoEntity? {
    val id = id ?: return null
    return CursoEntity(
        id = id,
        codigo = codigo.orEmpty(),
        nombre = nombre?.takeIf { it.isNotBlank() } ?: "Curso de inglés",
        nivel = NivelIngles.from(nivel),
        profesor = profesor.orEmpty(),
        aula = aula.orEmpty(),
        horario = horario.orEmpty()
    )
}

fun DashboardDto.toMatriculaEntity(estudianteId: Long): MatriculaEntity? {
    val datos = matricula ?: return null
    val id = datos.id ?: return null
    val cursoId = datos.cursoId ?: curso?.id ?: return null

    return MatriculaEntity(
        id = id,
        estudianteId = estudianteId,
        cursoId = cursoId,
        activa = true,
        progresoPorcentaje = datos.progresoPorcentaje ?: 0.0,
        promedio = datos.promedio ?: 0.0
    )
}

fun EvaluacionDto.toEntity(
    estudianteId: Long,
    cursoIdPredeterminado: Long
): EvaluacionEntity? {
    val id = id ?: return null
    val cursoId = cursoId ?: cursoIdPredeterminado
    val fecha = DateParser.aInstant(fechaEntrega) ?: return null

    return EvaluacionEntity(
        id = id,
        estudianteId = estudianteId,
        cursoId = cursoId,
        titulo = titulo?.takeIf { it.isNotBlank() } ?: "Evaluación",
        tipo = TipoEvaluacion.from(tipo),
        fechaEntregaMillis = fecha.toEpochMilli(),
        porcentaje = porcentaje ?: 0.0,
        nota = nota,
        leida = leida ?: false,
        completada = completada ?: false
    )
}

fun AsistenciaDto.toEntity(matriculaIdPredeterminada: Long): AsistenciaEntity? {
    val id = id ?: return null
    val fecha = DateParser.aInstant(fecha) ?: return null
    val matricula = matriculaId ?: matriculaIdPredeterminada

    return AsistenciaEntity(
        id = id,
        matriculaId = matricula,
        fechaMillis = fecha.toEpochMilli(),
        estado = EstadoAsistencia.from(estado),
        tema = tema?.takeIf { it.isNotBlank() } ?: "Sesión de clase"
    )
}
