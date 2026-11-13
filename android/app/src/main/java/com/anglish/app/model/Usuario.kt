package com.anglish.app.model

/**
 * Usuario autenticado que consume la app.
 *
 * Es un modelo de dominio: no tiene anotaciones de Room ni de Retrofit.
 * Los mapeos viven en data/mapper para mantener las capas desacopladas.
 */
data class Usuario(
    val id: Long,
    val nombres: String,
    val apellidos: String,
    val email: String,
    val rol: Rol,
    val nivelActual: NivelIngles,
    val avatarUrl: String? = null
) {
    val nombreCompleto: String
        get() = "$nombres $apellidos".trim()
}
