package com.anglish.app.data.local.model

import androidx.room.Embedded
import com.anglish.app.data.local.entity.AsistenciaEntity

/**
 * Asistencia enriquecida con el nombre del curso para mostrarla en la UI.
 */
data class AsistenciaConCurso(
    @Embedded val asistencia: AsistenciaEntity,
    val cursoNombre: String
)
