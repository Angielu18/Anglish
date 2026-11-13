package com.anglish.app.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey
import com.anglish.app.model.NivelIngles
import com.anglish.app.model.Rol

/**
 * Tabla local de usuarios (caché de la API).
 *
 * El correo es único para evitar duplicados cuando se sincroniza varias veces.
 */
@Entity(
    tableName = "usuarios",
    indices = [Index(value = ["email"], unique = true)]
)
data class UsuarioEntity(
    @PrimaryKey val id: Long,
    val nombres: String,
    val apellidos: String,
    val email: String,
    val rol: Rol,
    val nivelActual: NivelIngles,
    val avatarUrl: String? = null
)
