package com.anglish.app.data.local.dao

import androidx.room.Dao
import androidx.room.Query
import androidx.room.Upsert
import com.anglish.app.data.local.entity.AsistenciaEntity
import com.anglish.app.data.local.model.AsistenciaConCurso
import com.anglish.app.data.local.model.ResumenAsistenciaDb
import kotlinx.coroutines.flow.Flow

@Dao
interface AsistenciaDao {

    @Upsert
    suspend fun upsertAll(asistencias: List<AsistenciaEntity>)

    /**
     * Últimas asistencias del estudiante (máximo 5 para el Dashboard).
     */
    @Query(
        """
        SELECT a.*, c.nombre AS cursoNombre
        FROM asistencias a
        INNER JOIN matriculas m ON m.id = a.matriculaId
        INNER JOIN cursos c ON c.id = m.cursoId
        WHERE m.estudianteId = :estudianteId
        ORDER BY a.fechaMillis DESC
        LIMIT 5
        """
    )
    fun observarRecientes(estudianteId: Long): Flow<List<AsistenciaConCurso>>

    /**
     * Todas las asistencias del estudiante para la pantalla detallada.
     */
    @Query(
        """
        SELECT a.*, c.nombre AS cursoNombre
        FROM asistencias a
        INNER JOIN matriculas m ON m.id = a.matriculaId
        INNER JOIN cursos c ON c.id = m.cursoId
        WHERE m.estudianteId = :estudianteId
        ORDER BY a.fechaMillis DESC
        """
    )
    fun observarTodas(estudianteId: Long): Flow<List<AsistenciaConCurso>>

    /**
     * Agregado de asistencia calculado en SQLite.
     *
     * Porcentaje = (PRESENTE + TARDE + EXCUSA) / total * 100.
     * NULLIF evita dividir por cero cuando aún no hay sesiones.
     */
    @Query(
        """
        SELECT COUNT(*) AS total,
               COALESCE(SUM(CASE WHEN a.estado = 'PRESENTE' THEN 1 ELSE 0 END), 0) AS presentes,
               COALESCE(SUM(CASE WHEN a.estado = 'TARDE' THEN 1 ELSE 0 END), 0) AS tardes,
               COALESCE(SUM(CASE WHEN a.estado = 'AUSENTE' THEN 1 ELSE 0 END), 0) AS ausentes,
               COALESCE(SUM(CASE WHEN a.estado = 'EXCUSA' THEN 1 ELSE 0 END), 0) AS excusas,
               COALESCE(
                   ROUND(
                       SUM(CASE WHEN a.estado IN ('PRESENTE', 'TARDE', 'EXCUSA') THEN 1.0 ELSE 0.0 END)
                       * 100.0 / NULLIF(COUNT(*), 0),
                       1
                   ),
                   0.0
               ) AS porcentaje
        FROM asistencias a
        INNER JOIN matriculas m ON m.id = a.matriculaId
        WHERE m.estudianteId = :estudianteId
        """
    )
    fun observarResumen(estudianteId: Long): Flow<ResumenAsistenciaDb>

    /**
     * Borra el caché de asistencias del estudiante antes de una sincronización,
     * para que no queden filas obsoletas si el servidor eliminó una sesión.
     */
    @Query(
        """
        DELETE FROM asistencias
        WHERE matriculaId IN (
            SELECT id FROM matriculas WHERE estudianteId = :estudianteId
        )
        """
    )
    suspend fun eliminarPorEstudiante(estudianteId: Long)
}
