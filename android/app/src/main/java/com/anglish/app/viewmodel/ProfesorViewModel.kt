package com.anglish.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.anglish.app.data.local.dao.CursoDao
import com.anglish.app.data.mapper.toDomain
import com.anglish.app.data.session.SessionManager
import com.anglish.app.model.Curso
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn

data class ProfesorUiState(
    val nombreProfesor: String = "",
    val cursos: List<Curso> = emptyList(),
    val totalCursos: Int = 0,
    val asistenciaPromedio: Double = 85.7
)

class ProfesorViewModel(
    private val cursoDao: CursoDao,
    private val sessionManager: SessionManager
) : ViewModel() {

    val uiState: StateFlow<ProfesorUiState> = cursoDao.observarPorProfesor(sessionManager.nombres)
        .map { cursosEntity ->
            val cursos = cursosEntity.map { it.toDomain() }
            ProfesorUiState(
                nombreProfesor = "${sessionManager.nombres} ${sessionManager.apellidos}".trim(),
                cursos = cursos,
                totalCursos = cursos.size,
                asistenciaPromedio = 85.7
            )
        }
        .stateIn(
            scope = viewModelScope,
            started = SharingStarted.WhileSubscribed(5_000),
            initialValue = ProfesorUiState(
                nombreProfesor = "${sessionManager.nombres} ${sessionManager.apellidos}".trim()
            )
        )

    fun cerrarSesion() {
        sessionManager.cerrarSesion()
    }

    class Factory(
        private val cursoDao: CursoDao,
        private val sessionManager: SessionManager
    ) : ViewModelProvider.Factory {
        @Suppress("UNCHECKED_CAST")
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            return ProfesorViewModel(cursoDao, sessionManager) as T
        }
    }
}
