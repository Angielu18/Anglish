package com.anglish.app.data.local.model

import androidx.room.Embedded
import com.anglish.app.data.local.entity.EvaluacionEntity

/**
 * Evaluación enriquecida con el nombre del curso.
 */
data class EvaluacionConCurso(
    @Embedded val evaluacion: EvaluacionEntity,
    val cursoNombre: String
)
