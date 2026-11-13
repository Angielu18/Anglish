package com.anglish.app.data.local.dao

import androidx.room.Dao
import androidx.room.Query
import androidx.room.Upsert
import com.anglish.app.data.local.entity.CursoEntity
import com.anglish.app.data.local.model.CursoActivo
import kotlinx.coroutines.flow.Flow

@Dao
interface CursoDao {

    @Upsert
    suspend fun upsert(curso: CursoEntity)

    @Upsert
    suspend fun upsertAll(cursos: List<CursoEntity>)

    @Query("SELECT * FROM cursos ORDER BY codigo ASC")
    fun observarTodos(): Flow<List<CursoEntity>>

    @Query("SELECT * FROM cursos WHERE profesor LIKE '%' || :profesorNombre || '%' ORDER BY codigo ASC")
    fun observarPorProfesor(profesorNombre: String): Flow<List<CursoEntity>>

    /**
     * Devuelve el curso activo del estudiante junto con progreso y promedio
     * de su matrícula. Se usa Flow para que la UI se actualice sola cuando
     * cambie cualquier dato.
     */
    @Query(
        """
        SELECT c.*,
               m.id AS matriculaId,
               m.progresoPorcentaje AS progresoPorcentaje,
               m.promedio AS promedio
        FROM cursos c
        INNER JOIN matriculas m ON m.cursoId = c.id
        WHERE m.estudianteId = :estudianteId
          AND m.activa = 1
        LIMIT 1
        """
    )
    fun observarCursoActivo(estudianteId: Long): Flow<CursoActivo?>
}
