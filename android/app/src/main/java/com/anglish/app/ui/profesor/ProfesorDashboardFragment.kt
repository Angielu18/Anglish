package com.anglish.app.ui.profesor

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
import com.anglish.app.databinding.FragmentProfesorDashboardBinding
import com.anglish.app.model.Rol
import com.anglish.app.viewmodel.ProfesorViewModel
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import kotlinx.coroutines.launch
import java.util.Locale

class ProfesorDashboardFragment : Fragment() {

    private var _binding: FragmentProfesorDashboardBinding? = null
    private val binding get() = _binding!!

    private val viewModel: ProfesorViewModel by viewModels {
        val app = requireActivity().application as AnglishApplication
        ProfesorViewModel.Factory(
            app.container.cursoDao,
            app.container.sessionManager
        )
    }

    private val adapter = CursoProfesorAdapter()

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentProfesorDashboardBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.rvCursosProfesor.layoutManager = LinearLayoutManager(requireContext())
        binding.rvCursosProfesor.adapter = adapter

        val app = requireActivity().application as AnglishApplication
        val rolActual = app.container.sessionManager.rol

        configurarSegunRol(rolActual)

        binding.btnLogout.setOnClickListener {
            viewModel.cerrarSesion()
            findNavController().navigate(
                R.id.action_profesorDashboard_to_login,
                null,
                androidx.navigation.NavOptions.Builder()
                    .setPopUpTo(R.id.nav_graph, true)
                    .build()
            )
        }

        observarEstado(rolActual)
    }

    private fun configurarSegunRol(rol: Rol) {
        if (rol == Rol.ADMINISTRADOR) {
            binding.tvGreeting.text = "Módulo de Administrador"
            binding.tvRolBadge.text = "Administrador · Control Total"
            binding.tvModuloSeccionTitulo.text = "Módulo de Administrador"
            binding.btnModuloAccion1.text = "Gestionar entrevistas de nivelación"
            binding.btnModuloAccion2.text = "Gestión de acceso al sistema (Usuarios y Roles)"
            binding.btnModuloAccion3.isVisible = false

            binding.btnModuloAccion1.setOnClickListener {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Gestionar Entrevistas")
                    .setMessage(
                        "Entrevistas programadas en el sistema:\n\n" +
                        "1. Andrea Rincón Méndez\n   • Nivel interés: A2 | Modalidad: Virtual (Meet)\n   • Estado: PROGRAMADA (15/11/2026 10:00)\n\n" +
                        "2. Felipe Castro Guzmán\n   • Nivel interés: B1 | Modalidad: Presencial\n   • Estado: PROGRAMADA (16/11/2026 15:30)\n\n" +
                        "3. Mariana Silva Torres\n   • Estado: REALIZADA → Nivel sugerido: A1\n\n" +
                        "4. David Morales Cruz\n   • Estado: APROBADA → Nivel sugerido: B1"
                    )
                    .setPositiveButton("Cerrar", null)
                    .show()
            }

            binding.btnModuloAccion2.setOnClickListener {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Gestión de Acceso al Sistema (RBAC)")
                    .setMessage(
                        "Control de Usuarios, Roles y Permisos:\n\n" +
                        "• Angie Valentina Sotelo (admin@anglish.edu.co)\n  Rol: ADMINISTRADOR | Estado: ACTIVO\n\n" +
                        "• Laura Méndez (laura.mendez@anglish.edu.co)\n  Rol: PROFESOR | Estado: ACTIVO\n\n" +
                        "• Carlos Ríos (carlos.rios@anglish.edu.co)\n  Rol: PROFESOR | Estado: ACTIVO\n\n" +
                        "• Sara Gómez (sara.gomez@anglish.edu.co)\n  Rol: ESTUDIANTE | Estado: ACTIVO\n\n" +
                        "Total cuentas registradas: 17 usuarios en 3 roles."
                    )
                    .setPositiveButton("Cerrar", null)
                    .show()
            }
        } else {
            binding.tvGreeting.text = "Módulo de Profesores"
            binding.tvRolBadge.text = "Docente Titular"
            binding.tvModuloSeccionTitulo.text = "Módulo de Profesores"
            binding.btnModuloAccion1.text = "Revisar trabajos y calificar entregas"
            binding.btnModuloAccion2.text = "Gestionar tareas por curso y nivel"
            binding.btnModuloAccion3.text = "Reportes de rendimiento grupal"
            binding.btnModuloAccion3.isVisible = true

            binding.btnModuloAccion1.setOnClickListener {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Revisar Trabajos de Estudiantes")
                    .setMessage(
                        "Entregas recibidas en tus cursos:\n\n" +
                        "1. Sara Gómez (ING-A1-01)\n   • Actividad: Speaking: Presentación oral\n   • Archivo: speaking_sara_gomez.mp4\n   • Estado: PENDIENTE POR CALIFICAR\n\n" +
                        "2. Sara Gómez (ING-A1-01)\n   • Actividad: Taller de Vocabulario y Verb to be\n   • Nota: 4.5 / 5.0 (CALIFICADA)\n   • Retroalimentación: Excelente desarrollo."
                    )
                    .setPositiveButton("Cerrar", null)
                    .show()
            }

            binding.btnModuloAccion2.setOnClickListener {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Gestionar Tareas por Curso y Nivel")
                    .setMessage(
                        "Tareas asignadas:\n\n" +
                        "• [A1] ING-A1-01 — Taller de Vocabulario (15%)\n" +
                        "• [A1] ING-A1-01 — Quiz Unidad 1 (15%)\n" +
                        "• [A1] ING-A1-01 — Parcial Escrito (25%)\n" +
                        "• [A1] ING-A1-01 — Speaking: Rutina diaria (20%)\n" +
                        "• [B1] ING-B1-02 — Debate: Remote Work (20%)"
                    )
                    .setPositiveButton("Cerrar", null)
                    .show()
            }

            binding.btnModuloAccion3.setOnClickListener {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Reportes de Rendimiento por Grupo")
                    .setMessage(
                        "Consolidado Académico:\n\n" +
                        "• Curso ING-A1-01 (Nivel A1):\n  Promedio grupal: 3.6 / 5.0 | Asistencia: 82%\n\n" +
                        "• Curso ING-B1-02 (Nivel B1):\n  Promedio grupal: 4.1 / 5.0 | Asistencia: 89%\n\n" +
                        "Estudiantes en seguimiento preventivo:\n" +
                        "• Sara Gómez — Asistencia: 60% (Alerta < 80%)"
                    )
                    .setPositiveButton("Cerrar", null)
                    .show()
            }
        }
    }

    private fun observarEstado(rol: Rol) {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    val nombrePorDefecto = if (rol == Rol.ADMINISTRADOR) "Angie Valentina Sotelo" else "Prof. Laura Méndez"
                    binding.tvProfesorNombre.text = state.nombreProfesor.ifEmpty { nombrePorDefecto }
                    val iniciales = state.nombreProfesor
                        .split(" ")
                        .filter { it.isNotBlank() }
                        .take(2)
                        .map { it.first().uppercase() }
                        .joinToString("")
                    binding.tvAvatarProfesor.text = iniciales.ifEmpty { if (rol == Rol.ADMINISTRADOR) "AV" else "LM" }

                    binding.tvTotalCursos.text = state.totalCursos.toString()
                    binding.tvPromedioAsistencia.text = String.format(Locale.getDefault(), "%.1f%%", state.asistenciaPromedio)

                    adapter.submitList(state.cursos)
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
