package com.anglish.app.ui.dashboard

import android.content.res.ColorStateList
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.anglish.app.R
import com.anglish.app.databinding.ItemAsistenciaBinding
import com.anglish.app.model.Asistencia
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.ui.common.formatearFechaCorta

/**
 * Lista de asistencias recientes con semáforo de estado.
 */
class AsistenciaAdapter :
    ListAdapter<Asistencia, AsistenciaAdapter.ViewHolder>(DIFF) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemAsistenciaBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    inner class ViewHolder(
        private val binding: ItemAsistenciaBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: Asistencia) {
            val context = binding.root.context
            val color = ContextCompat.getColor(context, item.estado.colorRes())

            binding.tvTema.text = item.tema
            binding.tvFecha.text = context.getString(
                R.string.attendance_date_format,
                item.cursoNombre,
                formatearFechaCorta(context, item.fecha)
            )

            binding.tvEstado.text = context.getString(item.estado.labelRes())
            binding.tvEstado.backgroundTintList = ColorStateList.valueOf(color)
            binding.tvEstado.setTextColor(ContextCompat.getColor(context, R.color.white))
            binding.viewEstado.backgroundTintList = ColorStateList.valueOf(color)
        }
    }

    private fun EstadoAsistencia.colorRes(): Int = when (this) {
        EstadoAsistencia.PRESENTE -> R.color.success
        EstadoAsistencia.TARDE -> R.color.warning
        EstadoAsistencia.AUSENTE -> R.color.danger
        EstadoAsistencia.EXCUSA -> R.color.info
    }

    private fun EstadoAsistencia.labelRes(): Int = when (this) {
        EstadoAsistencia.PRESENTE -> R.string.attendance_present
        EstadoAsistencia.TARDE -> R.string.attendance_late
        EstadoAsistencia.AUSENTE -> R.string.attendance_absent
        EstadoAsistencia.EXCUSA -> R.string.attendance_excused
    }

    private companion object {
        val DIFF = object : DiffUtil.ItemCallback<Asistencia>() {
            override fun areItemsTheSame(oldItem: Asistencia, newItem: Asistencia): Boolean =
                oldItem.id == newItem.id

            override fun areContentsTheSame(oldItem: Asistencia, newItem: Asistencia): Boolean =
                oldItem == newItem
        }
    }
}
