package com.anglish.app.model

/**
 * Nivel de urgencia calculado en el dominio (no en la vista).
 *
 * Reglas:
 *  - VENCIDA : la fecha límite ya pasó.
 *  - HOY     : faltan menos de 24 horas (indicador rojo).
 *  - PROXIMA : faltan entre 24 y 48 horas (indicador amarillo).
 *  - NORMAL  : falta más de 48 horas (indicador verde).
 *
 * La vista solo traduce este enum a colores y textos.
 */
enum class Urgencia {
    VENCIDA,
    HOY,
    PROXIMA,
    NORMAL
}
