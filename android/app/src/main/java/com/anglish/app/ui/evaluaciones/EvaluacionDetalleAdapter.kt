package com.anglish.app.ui.evaluaciones

import android.content.res.ColorStateList
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.anglish.app.R
import com.anglish.app.databinding.ItemEvaluacionDetalleBinding
import com.anglish.app.model.EvaluacionAlerta
import com.anglish.app.model.Urgencia
import com.anglish.app.ui.common.formatearFechaEntrega
import java.util.Locale

class EvaluacionDetalleAdapter :
    ListAdapter<EvaluacionAlerta, EvaluacionDetalleAdapter.ViewHolder>(DIFF) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemEvaluacionDetalleBinding.inflate(
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
        private val binding: ItemEvaluacionDetalleBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: EvaluacionAlerta) {
            val context = binding.root.context

            binding.tvTitulo.text = item.titulo
            binding.tvTipoBadge.text = item.tipo.name
            binding.tvPorcentaje.text = String.format(Locale.getDefault(), "%d%% del curso", item.porcentaje.toInt())
            binding.tvFechaEntrega.text = formatearFechaEntrega(context, item.fechaEntrega)

            if (item.nota != null) {
                // Calificada
                binding.tvEstadoBadge.text = String.format(Locale.getDefault(), "Nota: %.1f", item.nota)
                val colorNota = if (item.nota >= 3.0) {
                    ContextCompat.getColor(context, R.color.success)
                } else {
                    ContextCompat.getColor(context, R.color.danger)
                }
                binding.tvEstadoBadge.backgroundTintList = ColorStateList.valueOf(colorNota)
            } else {
                // Pendiente con semáforo de urgencia
                val urgencia = item.urgencia()
                binding.tvEstadoBadge.text = context.getString(urgencia.labelRes())
                val colorUrgencia = ContextCompat.getColor(context, urgencia.colorRes())
                binding.tvEstadoBadge.backgroundTintList = ColorStateList.valueOf(colorUrgencia)
            }
        }
    }

    private fun Urgencia.colorRes(): Int = when (this) {
        Urgencia.VENCIDA -> R.color.danger_dark
        Urgencia.HOY -> R.color.danger
        Urgencia.PROXIMA -> R.color.warning
        Urgencia.NORMAL -> R.color.success
    }

    private fun Urgencia.labelRes(): Int = when (this) {
        Urgencia.VENCIDA -> R.string.urgency_overdue
        Urgencia.HOY -> R.string.urgency_today
        Urgencia.PROXIMA -> R.string.urgency_soon
        Urgencia.NORMAL -> R.string.urgency_normal
    }

    private companion object {
        val DIFF = object : DiffUtil.ItemCallback<EvaluacionAlerta>() {
            override fun areItemsTheSame(oldItem: EvaluacionAlerta, newItem: EvaluacionAlerta): Boolean =
                oldItem.id == newItem.id

            override fun areContentsTheSame(oldItem: EvaluacionAlerta, newItem: EvaluacionAlerta): Boolean =
                oldItem == newItem
        }
    }
}
