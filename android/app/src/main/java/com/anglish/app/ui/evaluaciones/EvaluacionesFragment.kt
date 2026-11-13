package com.anglish.app.ui.evaluaciones

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.content.ContextCompat
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
import com.anglish.app.databinding.FragmentEvaluacionesBinding
import com.anglish.app.viewmodel.EvaluacionesViewModel
import com.anglish.app.viewmodel.FiltroEvaluacion
import kotlinx.coroutines.launch
import java.util.Locale

class EvaluacionesFragment : Fragment() {

    private var _binding: FragmentEvaluacionesBinding? = null
    private val binding get() = _binding!!

    private val viewModel: EvaluacionesViewModel by viewModels {
        val app = requireActivity().application as AnglishApplication
        EvaluacionesViewModel.Factory(
            app.container.evaluacionDao,
            app.container.cursoDao,
            app.container.sessionManager
        )
    }

    private val adapter = EvaluacionDetalleAdapter()

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentEvaluacionesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.toolbar.setNavigationOnClickListener {
            findNavController().navigateUp()
        }

        binding.rvEvaluaciones.layoutManager = LinearLayoutManager(requireContext())
        binding.rvEvaluaciones.adapter = adapter

        configurarChips()
        observarEstado()
    }

    private fun configurarChips() {
        binding.chipGroupFiltro.setOnCheckedStateChangeListener { _, checkedIds ->
            when (checkedIds.firstOrNull()) {
                R.id.chipPendientes -> viewModel.aplicarFiltro(FiltroEvaluacion.PENDIENTES)
                R.id.chipCalificadas -> viewModel.aplicarFiltro(FiltroEvaluacion.CALIFICADAS)
                else -> viewModel.aplicarFiltro(FiltroEvaluacion.TODAS)
            }
        }
    }

    private fun observarEstado() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    binding.tvPromedio.text = String.format(Locale.getDefault(), "%.2f", state.promedio)

                    if (state.promedio >= 3.0) {
                        binding.tvEstadoAcademico.text = getString(R.string.academic_status_good)
                        binding.tvEstadoAcademico.setTextColor(
                            ContextCompat.getColor(requireContext(), R.color.success)
                        )
                    } else {
                        binding.tvEstadoAcademico.text = getString(R.string.academic_status_risk)
                        binding.tvEstadoAcademico.setTextColor(
                            ContextCompat.getColor(requireContext(), R.color.danger_dark)
                        )
                    }

                    adapter.submitList(state.evaluaciones)
                    binding.tvEmptyState.isVisible = state.evaluaciones.isEmpty()
                    binding.rvEvaluaciones.isVisible = state.evaluaciones.isNotEmpty()
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
