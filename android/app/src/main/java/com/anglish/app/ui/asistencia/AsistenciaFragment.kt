package com.anglish.app.ui.asistencia

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
import com.anglish.app.databinding.FragmentAsistenciaBinding
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.viewmodel.AsistenciaViewModel
import kotlinx.coroutines.launch
import java.util.Locale

class AsistenciaFragment : Fragment() {

    private var _binding: FragmentAsistenciaBinding? = null
    private val binding get() = _binding!!

    private val viewModel: AsistenciaViewModel by viewModels {
        val app = requireActivity().application as AnglishApplication
        AsistenciaViewModel.Factory(
            app.container.asistenciaDao,
            app.container.sessionManager
        )
    }

    private val adapter = AsistenciaDetalleAdapter()

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAsistenciaBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.toolbar.setNavigationOnClickListener {
            findNavController().navigateUp()
        }

        binding.rvAsistencias.layoutManager = LinearLayoutManager(requireContext())
        binding.rvAsistencias.adapter = adapter

        configurarChips()
        observarEstado()
    }

    private fun configurarChips() {
        binding.chipGroupFiltro.setOnCheckedStateChangeListener { _, checkedIds ->
            when (checkedIds.firstOrNull()) {
                R.id.chipPresentes -> viewModel.aplicarFiltro(EstadoAsistencia.PRESENTE)
                R.id.chipTardes -> viewModel.aplicarFiltro(EstadoAsistencia.TARDE)
                R.id.chipAusentes -> viewModel.aplicarFiltro(EstadoAsistencia.AUSENTE)
                R.id.chipExcusas -> viewModel.aplicarFiltro(EstadoAsistencia.EXCUSA)
                else -> viewModel.aplicarFiltro(null)
            }
        }
    }

    private fun observarEstado() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    val resumen = state.resumen
                    binding.tvPorcentajeTotal.text = String.format(Locale.getDefault(), "%.1f%%", resumen.porcentaje)
                    binding.tvConteoPresentes.text = resumen.presentes.toString()
                    binding.tvConteoTardes.text = resumen.tardes.toString()
                    binding.tvConteoAusentes.text = resumen.ausentes.toString()
                    binding.tvConteoExcusas.text = resumen.excusas.toString()

                    adapter.submitList(state.asistencias)
                    binding.tvEmptyState.isVisible = state.asistencias.isEmpty()
                    binding.rvAsistencias.isVisible = state.asistencias.isNotEmpty()
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
