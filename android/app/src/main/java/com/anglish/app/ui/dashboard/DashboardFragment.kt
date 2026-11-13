package com.anglish.app.ui.dashboard

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
import androidx.recyclerview.widget.LinearLayoutManager
import com.anglish.app.AnglishApplication
import com.anglish.app.R
import com.anglish.app.databinding.FragmentDashboardBinding
import com.anglish.app.model.DashboardEstudiante
import com.anglish.app.viewmodel.DashboardEvent
import com.anglish.app.viewmodel.DashboardUiState
import com.anglish.app.viewmodel.DashboardViewModel
import com.google.android.material.snackbar.Snackbar
import kotlinx.coroutines.launch
import java.text.NumberFormat
import kotlin.math.roundToInt

/**
 * Pantalla principal del rol Estudiante.
 *
 * Flujo unidireccional:
 *   UI (eventos) -> ViewModel -> Repository -> Room
 *   Room (Flow) -> ViewModel (StateFlow) -> UI (render)
 */
class DashboardFragment : Fragment() {

    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!

    private val viewModel: DashboardViewModel by viewModels {
        val app = requireActivity().application as AnglishApplication
        DashboardViewModel.Factory(
            app.container.dashboardRepository,
            app.container.sessionManager
        )
    }

    private val evaluacionAdapter = EvaluacionAdapter { alerta ->
        viewModel.marcarLeida(alerta.id)
    }
    private val asistenciaAdapter = AsistenciaAdapter()

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentDashboardBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        configurarListas()
        configurarEventos()
        observarEstado()
    }

    private fun configurarListas() {
        binding.rvEvaluaciones.apply {
            layoutManager = LinearLayoutManager(requireContext())
            adapter = evaluacionAdapter
            isNestedScrollingEnabled = false
        }

        binding.rvAsistencias.apply {
            layoutManager = LinearLayoutManager(requireContext())
            adapter = asistenciaAdapter
            isNestedScrollingEnabled = false
        }

        binding.swipeRefresh.setColorSchemeResources(R.color.anglish_teal)
    }

    private fun configurarEventos() {
        binding.swipeRefresh.setOnRefreshListener { viewModel.refrescar() }
        binding.btnReintentar.setOnClickListener { viewModel.reintentar() }

        binding.btnLogout.setOnClickListener {
            viewModel.cerrarSesion()
            findNavController().navigate(
                R.id.action_dashboard_to_login,
                null,
                androidx.navigation.NavOptions.Builder()
                    .setPopUpTo(R.id.nav_graph, true)
                    .build()
            )
        }

        binding.btnVerTodasAlertas.setOnClickListener {
            findNavController().navigate(R.id.action_dashboard_to_evaluaciones)
        }

        binding.btnVerTodasAsistencias.setOnClickListener {
            findNavController().navigate(R.id.action_dashboard_to_asistencia)
        }
    }

    private fun observarEstado() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.uiState.collect { estado -> renderizar(estado) }
                }
                launch {
                    viewModel.eventos.collect { evento -> mostrarEvento(evento) }
                }
            }
        }
    }

    private fun renderizar(estado: DashboardUiState) {
        when (estado) {
            DashboardUiState.Loading -> {
                binding.loadingContainer.isVisible = true
                binding.swipeRefresh.isVisible = false
                binding.errorCard.isVisible = false
            }

            is DashboardUiState.Error -> {
                binding.loadingContainer.isVisible = false
                binding.swipeRefresh.isVisible = false
                binding.errorCard.isVisible = true
                binding.tvError.text = estado.mensaje
            }

            is DashboardUiState.Success -> {
                binding.loadingContainer.isVisible = false
                binding.errorCard.isVisible = false
                binding.swipeRefresh.isVisible = true
                binding.swipeRefresh.isRefreshing = estado.refrescando
                binding.tvOffline.isVisible = estado.sinConexion
                pintarDashboard(estado.dashboard)
            }
        }
    }

    private fun pintarDashboard(dashboard: DashboardEstudiante) {
        val usuario = dashboard.usuario
        val curso = dashboard.curso

        binding.tvGreeting.text = getString(R.string.dashboard_greeting, usuario.nombres)
        binding.tvUserName.text = usuario.nombreCompleto
        binding.tvAvatar.text = iniciales(usuario.nombres, usuario.apellidos)
        binding.tvLevel.text = getString(R.string.level_format, usuario.nivelActual.name)

        binding.tvCourseName.text = curso?.nombre ?: getString(R.string.no_active_course)
        binding.tvProfessor.text = curso?.profesor
            ?.takeIf { it.isNotBlank() }
            ?.let { getString(R.string.professor_format, it) }
            ?: getString(R.string.no_professor)
        binding.tvSchedule.text = curso?.horario
            ?.takeIf { it.isNotBlank() }
            ?: getString(R.string.no_schedule)

        val progreso = dashboard.progresoPorcentaje.roundToInt().coerceIn(0, 100)
        binding.progressAcademico.progress = progreso
        binding.tvProgressValue.text = getString(R.string.percentage_format, progreso)
        binding.tvProgressDetail.text = resources.getQuantityString(
            R.plurals.progress_detail_format,
            dashboard.pendientes,
            dashboard.promedio,
            dashboard.pendientes
        )

        binding.tvPromedio.text = getString(R.string.average_format, dashboard.promedio)
        binding.tvAsistencia.text = getString(
            R.string.attendance_format,
            dashboard.resumenAsistencia.porcentaje
        )
        binding.tvPendientes.text = NumberFormat.getIntegerInstance().format(dashboard.pendientes)

        evaluacionAdapter.submitList(dashboard.evaluaciones)
        asistenciaAdapter.submitList(dashboard.asistenciasRecientes)

        binding.tvEmptyEvaluaciones.isVisible = dashboard.evaluaciones.isEmpty()
        binding.rvEvaluaciones.isVisible = dashboard.evaluaciones.isNotEmpty()
        binding.tvEmptyAsistencias.isVisible = dashboard.asistenciasRecientes.isEmpty()
        binding.rvAsistencias.isVisible = dashboard.asistenciasRecientes.isNotEmpty()
    }

    private fun mostrarEvento(evento: DashboardEvent) {
        val mensaje = when (evento) {
            DashboardEvent.DatosLocales -> R.string.event_offline
            DashboardEvent.AlertaMarcada -> R.string.event_alert_read
            DashboardEvent.ErrorDatosLocales -> R.string.event_local_data_error
        }
        Snackbar.make(binding.root, mensaje, Snackbar.LENGTH_LONG).show()
    }

    private fun iniciales(nombres: String, apellidos: String): String {
        val texto = listOf(nombres, apellidos)
            .mapNotNull { it.trim().firstOrNull()?.uppercaseChar() }
            .joinToString("")
        return texto.ifBlank { "A" }
    }

    override fun onDestroyView() {
        binding.rvEvaluaciones.adapter = null
        binding.rvAsistencias.adapter = null
        _binding = null
        super.onDestroyView()
    }
}
