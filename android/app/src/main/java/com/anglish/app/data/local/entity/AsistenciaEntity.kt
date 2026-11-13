package com.anglish.app.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index
import androidx.room.PrimaryKey
import com.anglish.app.model.EstadoAsistencia

/**
 * Asistencia a una sesión.
 *
 * Se guarda la fecha como Long (epoch millis) para que SQLite la ordene
 * eficientemente y para evitar problemas de zona horaria al persistir.
 */
@Entity(
    tableName = "asistencias",
    foreignKeys = [
        ForeignKey(
            entity = MatriculaEntity::class,
            parentColumns = ["id"],
            childColumns = ["matriculaId"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [
        Index("matriculaId"),
        Index("fechaMillis")
    ]
)
data class AsistenciaEntity(
    @PrimaryKey val id: Long,
    val matriculaId: Long,
    val fechaMillis: Long,
    val estado: EstadoAsistencia,
    val tema: String
)
