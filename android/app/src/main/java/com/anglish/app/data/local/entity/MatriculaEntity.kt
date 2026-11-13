package com.anglish.app.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index
import androidx.room.PrimaryKey

/**
 * Matrícula que une un estudiante con un curso.
 *
 * El progreso y el promedio viven aquí porque son datos de la matrícula,
 * no del curso ni del usuario por separado.
 */
@Entity(
    tableName = "matriculas",
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
        Index(value = ["estudianteId", "activa"])
    ]
)
data class MatriculaEntity(
    @PrimaryKey val id: Long,
    val estudianteId: Long,
    val cursoId: Long,
    val activa: Boolean,
    val progresoPorcentaje: Double,
    val promedio: Double
)
