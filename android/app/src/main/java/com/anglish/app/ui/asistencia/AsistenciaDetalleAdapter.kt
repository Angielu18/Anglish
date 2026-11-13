package com.anglish.app.ui.asistencia

import android.content.res.ColorStateList
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.anglish.app.R
import com.anglish.app.databinding.ItemAsistenciaDetalleBinding
import com.anglish.app.model.Asistencia
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.ui.common.formatearFechaLarga

class AsistenciaDetalleAdapter :
    ListAdapter<Asistencia, AsistenciaDetalleAdapter.ViewHolder>(DIFF) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemAsistenciaDetalleBinding.inflate(
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
        private val binding: ItemAsistenciaDetalleBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: Asistencia) {
            val context = binding.root.context
            val color = ContextCompat.getColor(context, item.estado.colorRes())

            binding.tvFecha.text = formatearFechaLarga(context, item.fecha)
            binding.tvTema.text = item.tema
            binding.tvCurso.text = item.cursoNombre

            binding.tvEstadoChip.text = context.getString(item.estado.labelRes())
            binding.tvEstadoChip.backgroundTintList = ColorStateList.valueOf(color)
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
