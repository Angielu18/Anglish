package com.anglish.app.ui.dashboard

import android.content.res.ColorStateList
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.anglish.app.R
import com.anglish.app.databinding.ItemEvaluacionBinding
import com.anglish.app.model.EvaluacionAlerta
import com.anglish.app.model.TipoEvaluacion
import com.anglish.app.model.Urgencia
import com.anglish.app.ui.common.formatearFechaEntrega
import kotlin.math.roundToInt

/**
 * Lista de alertas/evaluaciones con indicador visual de urgencia.
 *
 * El adaptador solo pinta datos ya calculados por el dominio. No consulta
 * la base de datos ni la red.
 */
class EvaluacionAdapter(
    private val alHacerClic: (EvaluacionAlerta) -> Unit
) : ListAdapter<EvaluacionAlerta, EvaluacionAdapter.ViewHolder>(DIFF) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemEvaluacionBinding.inflate(
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
        private val binding: ItemEvaluacionBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: EvaluacionAlerta) {
            val context = binding.root.context
            val urgencia = item.urgencia()
            val color = ContextCompat.getColor(context, urgencia.colorRes())

            binding.tvTitulo.text = item.titulo
            binding.tvCurso.text = item.cursoNombre
            binding.tvMeta.text = context.getString(
                R.string.evaluation_meta_format,
                context.getString(item.tipo.labelRes()),
                item.porcentaje.roundToInt()
            )
            binding.tvFecha.text = formatearFechaEntrega(context, item.fechaEntrega)

            binding.chipUrgencia.text = context.getString(urgencia.labelRes())
            binding.chipUrgencia.backgroundTintList = ColorStateList.valueOf(color)
            binding.chipUrgencia.setTextColor(ContextCompat.getColor(context, R.color.white))
            binding.viewUrgencia.setBackgroundColor(color)

            binding.imgLeida.isVisible = item.leida
            binding.root.alpha = if (item.leida) 0.72f else 1f

            binding.root.setOnClickListener { alHacerClic(item) }
            binding.root.contentDescription = context.getString(
                R.string.evaluation_content_description,
                item.titulo,
                context.getString(urgencia.labelRes())
            )
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

    private fun TipoEvaluacion.labelRes(): Int = when (this) {
        TipoEvaluacion.QUIZ -> R.string.type_quiz
        TipoEvaluacion.TALLER -> R.string.type_workshop
        TipoEvaluacion.PARCIAL -> R.string.type_midterm
        TipoEvaluacion.PROYECTO -> R.string.type_project
        TipoEvaluacion.EXAMEN_FINAL -> R.string.type_final_exam
    }

    private companion object {
        val DIFF = object : DiffUtil.ItemCallback<EvaluacionAlerta>() {
            override fun areItemsTheSame(
                oldItem: EvaluacionAlerta,
                newItem: EvaluacionAlerta
            ): Boolean = oldItem.id == newItem.id

            override fun areContentsTheSame(
                oldItem: EvaluacionAlerta,
                newItem: EvaluacionAlerta
            ): Boolean = oldItem == newItem
        }
    }
}
