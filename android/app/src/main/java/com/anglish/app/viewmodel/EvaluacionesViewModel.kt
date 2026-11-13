package com.anglish.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.anglish.app.data.local.dao.CursoDao
import com.anglish.app.data.local.dao.EvaluacionDao
import com.anglish.app.data.mapper.toDomain
import com.anglish.app.data.session.SessionManager
import com.anglish.app.model.EvaluacionAlerta
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.stateIn

enum class FiltroEvaluacion {
    TODAS,
    PENDIENTES,
    CALIFICADAS
}

data class EvaluacionesUiState(
    val promedio: Double = 0.0,
    val evaluaciones: List<EvaluacionAlerta> = emptyList(),
    val filtroActual: FiltroEvaluacion = FiltroEvaluacion.TODAS
)

class EvaluacionesViewModel(
    private val evaluacionDao: EvaluacionDao,
    private val cursoDao: CursoDao,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val filtro = MutableStateFlow(FiltroEvaluacion.TODAS)

    val uiState: StateFlow<EvaluacionesUiState> = combine(
        cursoDao.observarCursoActivo(sessionManager.usuarioId ?: 1L),
        evaluacionDao.observarTodas(sessionManager.usuarioId ?: 1L),
        filtro
    ) { cursoActivo, listaDb, filtroActual ->
        val listaDominio = listaDb.map { it.toDomain() }
        val filtradas = when (filtroActual) {
            FiltroEvaluacion.TODAS -> listaDominio
            FiltroEvaluacion.PENDIENTES -> listaDominio.filter { it.nota == null }
            FiltroEvaluacion.CALIFICADAS -> listaDominio.filter { it.nota != null }
        }

        EvaluacionesUiState(
            promedio = cursoActivo?.promedio ?: 0.0,
            evaluaciones = filtradas,
            filtroActual = filtroActual
        )
    }.stateIn(
        scope = viewModelScope,
        started = SharingStarted.WhileSubscribed(5_000),
        initialValue = EvaluacionesUiState()
    )

    fun aplicarFiltro(filtroNuevo: FiltroEvaluacion) {
        filtro.value = filtroNuevo
    }

    class Factory(
        private val evaluacionDao: EvaluacionDao,
        private val cursoDao: CursoDao,
        private val sessionManager: SessionManager
    ) : ViewModelProvider.Factory {
        @Suppress("UNCHECKED_CAST")
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            return EvaluacionesViewModel(evaluacionDao, cursoDao, sessionManager) as T
        }
    }
}
