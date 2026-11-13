package com.anglish.app.ui.profesor

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.anglish.app.databinding.ItemCursoProfesorBinding
import com.anglish.app.model.Curso

class CursoProfesorAdapter :
    ListAdapter<Curso, CursoProfesorAdapter.ViewHolder>(DIFF) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemCursoProfesorBinding.inflate(
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
        private val binding: ItemCursoProfesorBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: Curso) {
            binding.tvCodigoCurso.text = item.codigo
            binding.tvNombreCurso.text = item.nombre
            binding.tvNivelCurso.text = "Nivel ${item.nivel.name}"
            binding.tvHorario.text = item.horario
            binding.tvAula.text = item.aula
        }
    }

    private companion object {
        val DIFF = object : DiffUtil.ItemCallback<Curso>() {
            override fun areItemsTheSame(oldItem: Curso, newItem: Curso): Boolean =
                oldItem.id == newItem.id

            override fun areContentsTheSame(oldItem: Curso, newItem: Curso): Boolean =
                oldItem == newItem
        }
    }
}
