package com.anglish.app.viewmodel

import com.anglish.app.model.DashboardEstudiante

/**
 * Estado inmutable que la vista renderiza.
 * La UI nunca modifica este objeto: solo lo observa.
 */
sealed interface DashboardUiState {

    data object Loading : DashboardUiState

    data class Success(
        val dashboard: DashboardEstudiante,
        val refrescando: Boolean = false,
        val sinConexion: Boolean = false
    ) : DashboardUiState

    data class Error(val mensaje: String) : DashboardUiState
}

/**
 * Eventos de una sola vez (Snackbar). No forman parte del estado persistente.
 */
sealed interface DashboardEvent {
    data object DatosLocales : DashboardEvent
    data object AlertaMarcada : DashboardEvent
    data object ErrorDatosLocales : DashboardEvent
}
