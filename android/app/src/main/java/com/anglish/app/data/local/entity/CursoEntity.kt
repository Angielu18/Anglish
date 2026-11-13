package com.anglish.app.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.anglish.app.model.NivelIngles

/**
 * Catálogo de cursos.
 */
@Entity(tableName = "cursos")
data class CursoEntity(
    @PrimaryKey val id: Long,
    val codigo: String,
    val nombre: String,
    val nivel: NivelIngles,
    val profesor: String,
    val aula: String,
    val horario: String
)
