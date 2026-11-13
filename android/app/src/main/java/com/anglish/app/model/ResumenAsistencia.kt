package com.anglish.app.model

/**
 * Resumen calculado de asistencia.
 *
 * La app calcula estos valores en SQL dentro de Room (ver AsistenciaDao) para
 * no traer cientos de filas a memoria. El porcentaje usa la regla del proyecto:
 * (presentes + tardes + excusas) / total * 100.
 */
data class ResumenAsistencia(
    val total: Int = 0,
    val presentes: Int = 0,
    val tardes: Int = 0,
    val ausentes: Int = 0,
    val excusas: Int = 0,
    val porcentaje: Double = 0.0
) {
    val sesionesEfectivas: Int
        get() = presentes + tardes + excusas
}
