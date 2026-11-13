package com.anglish.app.model

/**
 * Roles del sistema Anglish.
 *
 * El backend es la autoridad: la app solo usa el rol para decidir qué pantalla
 * mostrar. Nunca se debe confiar únicamente en el rol guardado en el teléfono
 * para autorizar operaciones; la API debe validar el token y el permiso.
 */
enum class Rol {
    ADMINISTRADOR,
    PROFESOR,
    ESTUDIANTE;

    companion object {
        /**
         * Convierte el texto que llega de la API o de la sesión.
         * Si el valor es inválido se asume ESTUDIANTE (menor privilegio).
         */
        fun from(valor: String?): Rol =
            entries.firstOrNull { it.name.equals(valor, ignoreCase = true) } ?: ESTUDIANTE
    }
}
