package com.anglish.app.data.local.model

/**
 * Resultado de la consulta agregada de asistencia.
 * Los alias de la consulta SQL deben coincidir con estos nombres.
 */
data class ResumenAsistenciaDb(
    val total: Int,
    val presentes: Int,
    val tardes: Int,
    val ausentes: Int,
    val excusas: Int,
    val porcentaje: Double
)
