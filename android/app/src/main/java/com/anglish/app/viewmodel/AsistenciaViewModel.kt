package com.anglish.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.anglish.app.data.local.dao.AsistenciaDao
import com.anglish.app.data.mapper.toDomain
import com.anglish.app.data.session.SessionManager
import com.anglish.app.model.Asistencia
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.model.ResumenAsistencia
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.stateIn

data class AsistenciaUiState(
    val resumen: ResumenAsistencia = ResumenAsistencia(0, 0, 0, 0, 0, 0.0),
    val asistencias: List<Asistencia> = emptyList(),
    val filtroActual: EstadoAsistencia? = null
)

class AsistenciaViewModel(
    private val asistenciaDao: AsistenciaDao,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val filtro = MutableStateFlow<EstadoAsistencia?>(null)

    val uiState: StateFlow<AsistenciaUiState> = combine(
        asistenciaDao.observarResumen(sessionManager.usuarioId ?: 1L),
        asistenciaDao.observarTodas(sessionManager.usuarioId ?: 1L),
        filtro
    ) { resumenDb, listaDb, filtroActual ->
        val listaDominio = listaDb.map { it.toDomain() }
        val filtradas = if (filtroActual == null) {
            listaDominio
        } else {
            listaDominio.filter { it.estado == filtroActual }
        }

        AsistenciaUiState(
            resumen = resumenDb.toDomain(),
            asistencias = filtradas,
            filtroActual = filtroActual
        )
    }.stateIn(
        scope = viewModelScope,
        started = SharingStarted.WhileSubscribed(5_000),
        initialValue = AsistenciaUiState()
    )

    fun aplicarFiltro(estado: EstadoAsistencia?) {
        filtro.value = estado
    }

    class Factory(
        private val asistenciaDao: AsistenciaDao,
        private val sessionManager: SessionManager
    ) : ViewModelProvider.Factory {
        @Suppress("UNCHECKED_CAST")
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            return AsistenciaViewModel(asistenciaDao, sessionManager) as T
        }
    }
}
