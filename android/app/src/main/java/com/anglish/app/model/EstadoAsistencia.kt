package com.anglish.app.model

/**
 * Estados posibles de una sesión de clase.
 *
 * Para el porcentaje de asistencia se consideran efectivas PRESENTE, TARDE
 * y EXCUSA; AUSENTE es la única que resta.
 */
enum class EstadoAsistencia {
    PRESENTE,
    TARDE,
    AUSENTE,
    EXCUSA;

    val cuentaComoAsistencia: Boolean
        get() = this != AUSENTE

    companion object {
        fun from(valor: String?): EstadoAsistencia =
            entries.firstOrNull { it.name.equals(valor, ignoreCase = true) } ?: AUSENTE
    }
}
