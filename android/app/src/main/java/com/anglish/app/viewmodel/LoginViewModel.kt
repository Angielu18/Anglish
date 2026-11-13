package com.anglish.app.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.anglish.app.data.local.dao.UsuarioDao
import com.anglish.app.data.local.seed.DemoDataSeeder
import com.anglish.app.data.session.SessionManager
import com.anglish.app.model.Rol
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed interface LoginUiState {
    data object Idle : LoginUiState
    data object Loading : LoginUiState
    data class Success(val rol: Rol) : LoginUiState
    data class Error(val message: String) : LoginUiState
}

class LoginViewModel(
    private val usuarioDao: UsuarioDao,
    private val sessionManager: SessionManager,
    private val demoDataSeeder: DemoDataSeeder
) : ViewModel() {

    private val _uiState = MutableStateFlow<LoginUiState>(LoginUiState.Idle)
    val uiState: StateFlow<LoginUiState> = _uiState.asStateFlow()

    init {
        viewModelScope.launch {
            demoDataSeeder.seed()
        }
    }

    fun login(emailInput: String, passwordInput: String) {
        val email = emailInput.trim().lowercase()
        val password = passwordInput.trim()

        if (email.isBlank() || password.isBlank()) {
            _uiState.value = LoginUiState.Error("Ingresa tu correo institucional y contraseña.")
            return
        }

        if (password.length < 6) {
            _uiState.value = LoginUiState.Error("La contraseña debe tener al menos 6 caracteres.")
            return
        }

        viewModelScope.launch {
            _uiState.value = LoginUiState.Loading
            demoDataSeeder.seed()

            val usuario = usuarioDao.buscarPorEmail(email)
            val passwordValida = when (usuario?.rol) {
                Rol.ADMINISTRADOR -> password in listOf("Admin123*", "demo123")
                Rol.PROFESOR -> password in listOf("Profe123*", "Profesor123*", "demo123")
                Rol.ESTUDIANTE -> password in listOf("Estudiante123*", "demo123")
                null -> false
            }

            if (usuario != null && passwordValida) {
                sessionManager.guardarSesion(
                    usuarioId = usuario.id,
                    nombres = usuario.nombres,
                    apellidos = usuario.apellidos,
                    email = usuario.email,
                    rol = usuario.rol,
                    nivel = usuario.nivelActual,
                    token = "token-autenticado-${usuario.id}"
                )
                _uiState.value = LoginUiState.Success(usuario.rol)
            } else {
                _uiState.value = LoginUiState.Error("Credenciales inválidas. Verifica tu correo y contraseña según tu rol.")
            }
        }
    }

    class Factory(
        private val usuarioDao: UsuarioDao,
        private val sessionManager: SessionManager,
        private val demoDataSeeder: DemoDataSeeder
    ) : ViewModelProvider.Factory {
        @Suppress("UNCHECKED_CAST")
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            return LoginViewModel(usuarioDao, sessionManager, demoDataSeeder) as T
        }
    }
}
