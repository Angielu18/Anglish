package com.anglish.app.data.local.dao

import androidx.room.Dao
import androidx.room.Query
import androidx.room.Upsert
import com.anglish.app.data.local.entity.UsuarioEntity
import com.anglish.app.model.Rol
import kotlinx.coroutines.flow.Flow

@Dao
interface UsuarioDao {

    @Query("SELECT * FROM usuarios WHERE id = :id LIMIT 1")
    fun observarPorId(id: Long): Flow<UsuarioEntity?>

    @Query("SELECT * FROM usuarios WHERE email = :email LIMIT 1")
    suspend fun buscarPorEmail(email: String): UsuarioEntity?

    @Query("SELECT * FROM usuarios WHERE rol = :rol")
    fun observarPorRol(rol: Rol): Flow<List<UsuarioEntity>>

    @Query("SELECT COUNT(*) FROM usuarios")
    suspend fun contar(): Int

    /**
     * @Upsert actualiza si existe o inserta si no.
     * No se usa REPLACE porque REPLACE borra la fila anterior y activaría
     * los ON DELETE CASCADE de matrículas, asistencias y evaluaciones.
     */
    @Upsert
    suspend fun upsert(usuario: UsuarioEntity)
}
