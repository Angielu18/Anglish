package com.anglish.app.data.repository

import androidx.room.withTransaction
import com.anglish.app.data.local.AnglishDatabase
import com.anglish.app.data.local.dao.AsistenciaDao
import com.anglish.app.data.local.dao.CursoDao
import com.anglish.app.data.local.dao.EvaluacionDao
import com.anglish.app.data.local.dao.MatriculaDao
import com.anglish.app.data.local.dao.UsuarioDao
import com.anglish.app.data.local.seed.DemoDataSeeder
import com.anglish.app.data.mapper.toDomain
import com.anglish.app.data.mapper.toEntity
import com.anglish.app.data.mapper.toMatriculaEntity
import com.anglish.app.data.remote.AnglishApiService
import com.anglish.app.data.session.SessionManager
import com.anglish.app.model.DashboardEstudiante
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.CoroutineDispatcher
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.flowOn
import kotlinx.coroutines.withContext

/**
 * Repositorio del Dashboard.
 *
 * Patrón offline-first:
 *  1. Room es la fuente de verdad que observa la UI.
 *  2. La API se consulta en segundo plano y escribe en Room.
 *  3. Si la API falla, Room sigue entregando los datos guardados.
 *
 * Esto garantiza que el Fragment nunca dependa de la red para renderizar.
 */
class DashboardRepository(
    private val database: AnglishDatabase,
    private val usuarioDao: UsuarioDao,
    private val cursoDao: CursoDao,
    private val matriculaDao: MatriculaDao,
    private val asistenciaDao: AsistenciaDao,
    private val evaluacionDao: EvaluacionDao,
    private val api: AnglishApiService,
    private val sessionManager: SessionManager,
    private val demoDataSeeder: DemoDataSeeder,
    private val ioDispatcher: CoroutineDispatcher = Dispatchers.IO
) {

    /**
     * Inserta los datos de demostración solo si la base local está vacía.
     */
    suspend fun asegurarDatosDemo() = withContext(ioDispatcher) {
        if (usuarioDao.contar() == 0) {
            database.withTransaction {
                demoDataSeeder.seed()
            }
        }
    }

    /**
     * Flujo reactivo que alimenta el ViewModel.
     * Room vuelve a emitir automáticamente cuando cambia cualquier tabla.
     */
    fun observarDashboard(estudianteId: Long): Flow<DashboardEstudiante?> =
        combine(
            usuarioDao.observarPorId(estudianteId),
            cursoDao.observarCursoActivo(estudianteId),
            evaluacionDao.observarPorEstudiante(estudianteId),
            asistenciaDao.observarRecientes(estudianteId),
            asistenciaDao.observarResumen(estudianteId)
        ) { usuario, cursoActivo, evaluaciones, asistencias, resumen ->
            if (usuario == null) {
                null
            } else {
                DashboardEstudiante(
                    usuario = usuario.toDomain(),
                    curso = cursoActivo?.curso?.toDomain(),
                    matriculaId = cursoActivo?.matriculaId,
                    progresoPorcentaje = cursoActivo?.progresoPorcentaje ?: 0.0,
                    promedio = cursoActivo?.promedio ?: 0.0,
                    resumenAsistencia = resumen.toDomain(),
                    evaluaciones = evaluaciones.map { it.toDomain() },
                    asistenciasRecientes = asistencias.map { it.toDomain() }
                )
            }
        }.flowOn(ioDispatcher)

    /**
     * Sincroniza el Dashboard desde la API y actualiza Room en una transacción.
     * Devuelve Result para que el ViewModel decida si muestra el aviso offline.
     */
    suspend fun sincronizar(estudianteId: Long): Result<Unit> = withContext(ioDispatcher) {
        try {
            val dto = api.obtenerDashboard(estudianteId)

            val usuario = dto.estudiante?.toEntity()
                ?: error("La respuesta del servidor no incluyó el estudiante.")

            val curso = dto.curso?.toEntity()
            val matricula = dto.toMatriculaEntity(estudianteId)

            database.withTransaction {
                usuarioDao.upsert(usuario)

                if (curso != null) {
                    cursoDao.upsert(curso)
                }

                if (curso != null && matricula != null) {
                    matriculaDao.upsert(matricula)

                    // Se reemplaza el caché del estudiante para reflejar
                    // eliminaciones hechas en el servidor.
                    asistenciaDao.eliminarPorEstudiante(estudianteId)
                    evaluacionDao.eliminarPorEstudiante(estudianteId)

                    val asistencias = dto.asistencias
                        .orEmpty()
                        .mapNotNull { it.toEntity(matricula.id) }
                    if (asistencias.isNotEmpty()) {
                        asistenciaDao.upsertAll(asistencias)
                    }

                    val evaluaciones = dto.evaluaciones
                        .orEmpty()
                        .mapNotNull { it.toEntity(estudianteId, curso.id) }
                    if (evaluaciones.isNotEmpty()) {
                        evaluacionDao.upsertAll(evaluaciones)
                    }
                }
            }

            sessionManager.actualizarNivel(usuario.nivelActual)
            Result.success(Unit)
        } catch (cancelacion: CancellationException) {
            throw cancelacion
        } catch (error: Exception) {
            Result.failure(error)
        }
    }

    /**
     * Marca una alerta como leída. Room emite el cambio y la UI se actualiza
     * sin volver a consultar la red.
     */
    suspend fun marcarEvaluacionLeida(evaluacionId: Long, leida: Boolean = true) {
        withContext(ioDispatcher) {
            evaluacionDao.actualizarLeida(evaluacionId, leida)
        }
    }
}
