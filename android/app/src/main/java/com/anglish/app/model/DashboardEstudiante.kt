package com.anglish.app.model

/**
 * Estado completo que alimenta el Dashboard del estudiante.
 *
 * Es un modelo de solo lectura construido por el repositorio a partir de Room.
 * La UI no consulta la base de datos directamente.
 */
data class DashboardEstudiante(
    val usuario: Usuario,
    val curso: Curso?,
    val matriculaId: Long?,
    val progresoPorcentaje: Double,
    val promedio: Double,
    val resumenAsistencia: ResumenAsistencia,
    val evaluaciones: List<EvaluacionAlerta>,
    val asistenciasRecientes: List<Asistencia>
) {
    /** Actividades que todavía no han sido completadas. */
    val pendientes: Int
        get() = evaluaciones.count { !it.completada }

    /** Alertas nuevas que el estudiante aún no ha abierto. */
    val sinLeer: Int
        get() = evaluaciones.count { !it.leida }
}
