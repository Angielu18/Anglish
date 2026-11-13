package com.anglish.app.ui.login

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.navigation.fragment.findNavController
import com.anglish.app.AnglishApplication
import com.anglish.app.R
import com.anglish.app.databinding.FragmentLoginBinding
import com.anglish.app.model.Rol
import com.anglish.app.viewmodel.LoginUiState
import com.anglish.app.viewmodel.LoginViewModel
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import kotlinx.coroutines.launch

class LoginFragment : Fragment() {

    private var _binding: FragmentLoginBinding? = null
    private val binding get() = _binding!!

    private val viewModel: LoginViewModel by viewModels {
        val app = requireActivity().application as AnglishApplication
        LoginViewModel.Factory(
            app.container.usuarioDao,
            app.container.sessionManager,
            app.container.demoDataSeeder
        )
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLoginBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val app = requireActivity().application as AnglishApplication
        if (app.container.sessionManager.estaAutenticado) {
            navegarSegunRol(app.container.sessionManager.rol)
            return
        }

        configurarBotones()
        observarEstado()
    }

    private fun configurarBotones() {
        binding.btnLogin.setOnClickListener {
            val email = binding.etEmail.text?.toString().orEmpty()
            val pass = binding.etPassword.text?.toString().orEmpty()
            viewModel.login(email, pass)
        }

        binding.btnVisitorInfo.setOnClickListener {
            mostrarModalVisitante()
        }
    }

    private fun mostrarModalVisitante() {
        val infoPublica = """
            ¿QUIÉNES SOMOS?
            Anglish es una academia colombiana dedicada a la enseñanza integral del idioma inglés bajo estándares del Marco Común Europeo (MCER).

            NUESTROS PROGRAMAS (A1 a C1):
            • Nivel A1 (Principiante): Expresiones cotidianas y bases gramaticales (80h).
            • Nivel A2 (Básico): Comunicación en tareas simples y entorno cercano (80h).
            • Nivel B1 (Pre-intermedio): Desenvolvimiento en viajes, estudio y trabajo (90h).
            • Nivel B2 (Intermedio Alto): Fluidez y argumentación técnica/profesional (100h).
            • Nivel C1 (Avanzado): Dominio operativo eficaz en ámbitos académicos y corporativos (120h).

            OBJETIVOS PEDAGÓGICOS:
            • Desarrollo equilibrado de Speaking, Listening, Reading y Writing.
            • Grupos personalizados (máx. 12–15 estudiantes).
            • Seguimiento digital continuo de asistencia, tareas y evaluaciones.

            Nota: En Modo Visitante solo puedes consultar información pública. Para acceder a servicios internos, inicia sesión con tu cuenta institucional.
        """.trimIndent()

        MaterialAlertDialogBuilder(requireContext())
            .setTitle("Anglish · Información Pública (Visitante)")
            .setMessage(infoPublica)
            .setPositiveButton("Entendido", null)
            .show()
    }

    private fun observarEstado() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    when (state) {
                        is LoginUiState.Idle -> {
                            binding.loginProgress.isVisible = false
                            binding.btnLogin.isEnabled = true
                            binding.tvErrorLogin.isVisible = false
                        }
                        is LoginUiState.Loading -> {
                            binding.loginProgress.isVisible = true
                            binding.btnLogin.isEnabled = false
                            binding.tvErrorLogin.isVisible = false
                        }
                        is LoginUiState.Success -> {
                            binding.loginProgress.isVisible = false
                            binding.btnLogin.isEnabled = true
                            binding.tvErrorLogin.isVisible = false
                            navegarSegunRol(state.rol)
                        }
                        is LoginUiState.Error -> {
                            binding.loginProgress.isVisible = false
                            binding.btnLogin.isEnabled = true
                            binding.tvErrorLogin.text = state.message
                            binding.tvErrorLogin.isVisible = true
                        }
                    }
                }
            }
        }
    }

    private fun navegarSegunRol(rol: Rol) {
        val nav = findNavController()
        when (rol) {
            Rol.PROFESOR, Rol.ADMINISTRADOR -> {
                nav.navigate(R.id.action_login_to_profesorDashboard)
            }
            Rol.ESTUDIANTE -> {
                nav.navigate(R.id.action_login_to_dashboard)
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
