package com.anglish.app.data.local.dao

import androidx.room.Dao
import androidx.room.Query
import androidx.room.Upsert
import com.anglish.app.data.local.entity.EvaluacionEntity
import com.anglish.app.data.local.model.EvaluacionConCurso
import kotlinx.coroutines.flow.Flow

@Dao
interface EvaluacionDao {

    @Upsert
    suspend fun upsertAll(evaluaciones: List<EvaluacionEntity>)

    /**
     * Evaluaciones no completadas, ordenadas por fecha límite.
     * La UI calcula la urgencia con EvaluacionAlerta.urgencia().
     */
    @Query(
        """
        SELECT e.*, c.nombre AS cursoNombre
        FROM evaluaciones e
        INNER JOIN cursos c ON c.id = e.cursoId
        WHERE e.estudianteId = :estudianteId
          AND e.completada = 0
        ORDER BY e.fechaEntregaMillis ASC
        """
    )
    fun observarPorEstudiante(estudianteId: Long): Flow<List<EvaluacionConCurso>>

    /**
     * Todas las evaluaciones (pendientes y calificadas) para la pantalla de calificaciones.
     */
    @Query(
        """
        SELECT e.*, c.nombre AS cursoNombre
        FROM evaluaciones e
        INNER JOIN cursos c ON c.id = e.cursoId
        WHERE e.estudianteId = :estudianteId
        ORDER BY e.fechaEntregaMillis DESC
        """
    )
    fun observarTodas(estudianteId: Long): Flow<List<EvaluacionConCurso>>

    @Query("UPDATE evaluaciones SET leida = :leida WHERE id = :evaluacionId")
    suspend fun actualizarLeida(evaluacionId: Long, leida: Boolean)

    @Query("DELETE FROM evaluaciones WHERE estudianteId = :estudianteId")
    suspend fun eliminarPorEstudiante(estudianteId: Long)
}
