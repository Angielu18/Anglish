package com.anglish.app.model

/**
 * Curso activo del estudiante.
 */
data class Curso(
    val id: Long,
    val codigo: String,
    val nombre: String,
    val nivel: NivelIngles,
    val profesor: String,
    val aula: String,
    val horario: String
)
