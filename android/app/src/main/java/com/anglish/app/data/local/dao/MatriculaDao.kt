package com.anglish.app.data.local.dao

import androidx.room.Dao
import androidx.room.Query
import androidx.room.Upsert
import com.anglish.app.data.local.entity.MatriculaEntity

@Dao
interface MatriculaDao {

    @Upsert
    suspend fun upsert(matricula: MatriculaEntity)

    @Query("SELECT COUNT(*) FROM matriculas")
    suspend fun contar(): Int
}
