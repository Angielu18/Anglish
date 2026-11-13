package com.anglish.app.data.remote.dto

import com.google.gson.annotations.SerializedName

/**
 * Contrato JSON esperado del endpoint:
 *   GET /api/v1/estudiantes/{id}/dashboard
 *
 * Los campos son nullable a propósito: Gson puede dejar null si el servidor
 * omite un dato y el mapeador decide si es un error o un valor por defecto.
 */
data class DashboardDto(
    @SerializedName("estudiante") val estudiante: EstudianteDto? = null,
    @SerializedName("curso") val curso: CursoDto? = null,
    @SerializedName("matricula") val matricula: MatriculaDto? = null,
    @SerializedName("evaluaciones") val evaluaciones: List<EvaluacionDto>? = null,
    @SerializedName("asistencias") val asistencias: List<AsistenciaDto>? = null
)

data class EstudianteDto(
    @SerializedName("id") val id: Long? = null,
    @SerializedName("nombres") val nombres: String? = null,
    @SerializedName("apellidos") val apellidos: String? = null,
    @SerializedName("email") val email: String? = null,
    @SerializedName("rol") val rol: String? = null,
    @SerializedName("nivel") val nivel: String? = null,
    @SerializedName("avatar_url") val avatarUrl: String? = null
)

data class CursoDto(
    @SerializedName("id") val id: Long? = null,
    @SerializedName("codigo") val codigo: String? = null,
    @SerializedName("nombre") val nombre: String? = null,
    @SerializedName("nivel") val nivel: String? = null,
    @SerializedName("profesor") val profesor: String? = null,
    @SerializedName("aula") val aula: String? = null,
    @SerializedName("horario") val horario: String? = null
)

data class MatriculaDto(
    @SerializedName("id") val id: Long? = null,
    @SerializedName("curso_id") val cursoId: Long? = null,
    @SerializedName("progreso_porcentaje") val progresoPorcentaje: Double? = null,
    @SerializedName("promedio") val promedio: Double? = null
)

data class EvaluacionDto(
    @SerializedName("id") val id: Long? = null,
    @SerializedName("curso_id") val cursoId: Long? = null,
    @SerializedName("curso_nombre") val cursoNombre: String? = null,
    @SerializedName("titulo") val titulo: String? = null,
    @SerializedName("tipo") val tipo: String? = null,
    @SerializedName("fecha_entrega") val fechaEntrega: String? = null,
    @SerializedName("porcentaje") val porcentaje: Double? = null,
    @SerializedName("nota") val nota: Double? = null,
    @SerializedName("leida") val leida: Boolean? = null,
    @SerializedName("completada") val completada: Boolean? = null
)

data class AsistenciaDto(
    @SerializedName("id") val id: Long? = null,
    @SerializedName("matricula_id") val matriculaId: Long? = null,
    @SerializedName("curso_nombre") val cursoNombre: String? = null,
    @SerializedName("fecha") val fecha: String? = null,
    @SerializedName("estado") val estado: String? = null,
    @SerializedName("tema") val tema: String? = null
)
