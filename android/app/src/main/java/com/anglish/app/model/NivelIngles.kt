package com.anglish.app.model

/**
 * Niveles del Marco Común Europeo de Referencia usados por la academia.
 */
enum class NivelIngles(val descripcion: String) {
    A1("Principiante"),
    A2("Básico"),
    B1("Intermedio"),
    B2("Intermedio alto"),
    C1("Avanzado");

    companion object {
        fun from(valor: String?): NivelIngles =
            entries.firstOrNull { it.name.equals(valor, ignoreCase = true) } ?: A1
    }
}
