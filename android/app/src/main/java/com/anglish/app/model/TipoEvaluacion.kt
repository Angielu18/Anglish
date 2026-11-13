package com.anglish.app.model

/**
 * Tipos de evaluación que puede publicar un profesor.
 */
enum class TipoEvaluacion {
    QUIZ,
    TALLER,
    PARCIAL,
    PROYECTO,
    EXAMEN_FINAL;

    companion object {
        fun from(valor: String?): TipoEvaluacion =
            entries.firstOrNull { it.name.equals(valor, ignoreCase = true) } ?: TALLER
    }
}
