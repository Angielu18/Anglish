package com.anglish.app.model

import java.time.Instant

/**
 * Registro de asistencia a una sesión de clase.
 */
data class Asistencia(
    val id: Long,
    val cursoNombre: String,
    val fecha: Instant,
    val estado: EstadoAsistencia,
    val tema: String
)
