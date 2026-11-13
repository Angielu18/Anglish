package com.anglish.app.data.local.model

import androidx.room.Embedded
import com.anglish.app.data.local.entity.CursoEntity

/**
 * Resultado del JOIN cursos + matriculas para el curso activo del estudiante.
 */
data class CursoActivo(
    @Embedded val curso: CursoEntity,
    val matriculaId: Long,
    val progresoPorcentaje: Double,
    val promedio: Double
)
