package com.anglish.app.viewmodel

import android.util.Log
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.anglish.app.data.repository.DashboardRepository
import com.anglish.app.data.session.SessionManager
import com.anglish.app.model.DashboardEstudiante
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.coroutines.flow.catch
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch

/**
 * ViewModel del Dashboard del estudiante.
 *
 * Responsabilidades:
 *  - Orquestar el repositorio (no conoce Room ni Retrofit).
 *  - Exponer un StateFlow con Loading / Success / Error.
 *  - Ejecutar la sincronización en viewModelScope.
 *  - Emitir eventos puntuales para Snackbars.
 *
 * La vista solo llama métodos públicos y observa uiState: flujo unidireccional.
 */
class DashboardViewModel(
    private val repository: DashboardRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val estudianteId: Long = sessionManager.usuarioId ?: ESTUDIANTE_DEMO_ID

    fun cerrarSesion() {
        sessionManager.cerrarSesion()
    }

    private val refrescando = MutableStateFlow(false)
    private val sinConexion = MutableStateFlow(false)

    private val _eventos = MutableSharedFlow<DashboardEvent>(
        extraBufferCapacity = 1,
        onBufferOverflow = kotlinx.coroutines.channels.BufferOverflow.DROP_OLDEST
    )
    val eventos = _eventos.asSharedFlow()

    /**
     * Result envuelve el flujo para poder representar también un error de Room
     * sin romper el StateFlow.
     */
    private val dashboardFlow: Flow<Result<DashboardEstudiante?>> =
        repository.observarDashboard(estudianteId)
            .map { dashboard -> Result.success<DashboardEstudiante?>(dashboard) }
            .catch { error -> emit(Result.failure<DashboardEstudiante?>(error)) }

    val uiState = combine(
        dashboardFlow,
        refrescando,
        sinConexion
    ) { resultado, estaRefrescando, estaSinConexion ->
        resultado.fold(
            onSuccess = { dashboard ->
                if (dashboard == null) {
                    DashboardUiState.Loading
                } else {
                    DashboardUiState.Success(
                        dashboard = dashboard,
                        refrescando = estaRefrescando,
                        sinConexion = estaSinConexion
                    )
                }
            },
            onFailure = { error ->
                DashboardUiState.Error(
                    error.message ?: "No se pudo cargar la información del panel."
                )
            }
        )
    }.stateIn(
        scope = viewModelScope,
        started = SharingStarted.WhileSubscribed(5_000),
        initialValue = DashboardUiState.Loading
    )

    init {
        viewModelScope.launch {
            try {
                repository.asegurarDatosDemo()
            } catch (cancelacion: CancellationException) {
                throw cancelacion
            } catch (error: Exception) {
                Log.e(TAG, "No se pudieron crear los datos locales", error)
                _eventos.tryEmit(DashboardEvent.ErrorDatosLocales)
            }

            sincronizar(notificarFallo = false)
        }
    }

    /**
     * Se llama desde el SwipeRefreshLayout o desde el botón Reintentar.
     */
    fun refrescar() {
        if (refrescando.value) return
        viewModelScope.launch {
            sincronizar(notificarFallo = true)
        }
    }

    fun reintentar() {
        refrescar()
    }

    /**
     * Marca una alerta como leída. Room emite el cambio y la lista se actualiza.
     */
    fun marcarLeida(evaluacionId: Long) {
        viewModelScope.launch {
            val resultado = runCatching {
                repository.marcarEvaluacionLeida(evaluacionId, leida = true)
            }
            if (resultado.isSuccess) {
                _eventos.tryEmit(DashboardEvent.AlertaMarcada)
            } else {
                Log.e(TAG, "No se pudo marcar la alerta como leída", resultado.exceptionOrNull())
            }
        }
    }

    private suspend fun sincronizar(notificarFallo: Boolean) {
        refrescando.value = true
        try {
            val resultado = repository.sincronizar(estudianteId)
            sinConexion.value = resultado.isFailure

            if (resultado.isFailure) {
                Log.w(TAG, "Sincronización remota no disponible", resultado.exceptionOrNull())
                if (notificarFallo) {
                    _eventos.tryEmit(DashboardEvent.DatosLocales)
                }
            }
        } finally {
            refrescando.value = false
        }
    }

    /**
     * Fábrica manual para inyectar el repositorio sin Hilt.
     * Se usa desde DashboardFragment con viewModels { ... }.
     */
    class Factory(
        private val repository: DashboardRepository,
        private val sessionManager: SessionManager
    ) : ViewModelProvider.Factory {

        @Suppress("UNCHECKED_CAST")
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            if (modelClass.isAssignableFrom(DashboardViewModel::class.java)) {
                return DashboardViewModel(repository, sessionManager) as T
            }
            throw IllegalArgumentException("ViewModel no soportado: ${modelClass.name}")
        }
    }

    private companion object {
        const val TAG = "DashboardViewModel"
        const val ESTUDIANTE_DEMO_ID = 1L
    }
}
