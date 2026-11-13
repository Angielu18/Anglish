package com.anglish.app.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index
import androidx.room.PrimaryKey
import com.anglish.app.model.TipoEvaluacion

/**
 * Evaluación o entrega pendiente del estudiante.
 *
 * - leida: el estudiante ya abrió la alerta (solo cambia la presentación).
 * - completada: la actividad ya fue entregada/calificada y deja de aparecer.
 */
@Entity(
    tableName = "evaluaciones",
    foreignKeys = [
        ForeignKey(
            entity = UsuarioEntity::class,
            parentColumns = ["id"],
            childColumns = ["estudianteId"],
            onDelete = ForeignKey.CASCADE
        ),
        ForeignKey(
            entity = CursoEntity::class,
            parentColumns = ["id"],
            childColumns = ["cursoId"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [
        Index("estudianteId"),
        Index("cursoId"),
        Index(value = ["estudianteId", "completada"])
    ]
)
data class EvaluacionEntity(
    @PrimaryKey val id: Long,
    val estudianteId: Long,
    val cursoId: Long,
    val titulo: String,
    val tipo: TipoEvaluacion,
    val fechaEntregaMillis: Long,
    val porcentaje: Double,
    val nota: Double? = null,
    val leida: Boolean = false,
    val completada: Boolean = false
)
