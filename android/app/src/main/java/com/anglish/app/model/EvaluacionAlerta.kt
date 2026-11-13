package com.anglish.app.model

import java.time.Duration
import java.time.Instant

/**
 * Alerta de evaluación o entrega pendiente.
 *
 * El cálculo de urgencia está en el modelo para poder probarlo con JUnit sin
 * depender de Android ni de la interfaz.
 */
data class EvaluacionAlerta(
    val id: Long,
    val cursoNombre: String,
    val titulo: String,
    val tipo: TipoEvaluacion,
    val fechaEntrega: Instant,
    val porcentaje: Double,
    val nota: Double? = null,
    val leida: Boolean = false,
    val completada: Boolean = false
) {
    /**
     * @param ahora se recibe como parámetro para que las pruebas sean
     * deterministas y no dependan del reloj del dispositivo.
     */
    fun urgencia(ahora: Instant = Instant.now()): Urgencia {
        if (!fechaEntrega.isAfter(ahora)) return Urgencia.VENCIDA

        val horasRestantes = Duration.between(ahora, fechaEntrega).toHours()
        return when {
            horasRestantes < 24 -> Urgencia.HOY
            horasRestantes <= 48 -> Urgencia.PROXIMA
            else -> Urgencia.NORMAL
        }
    }
}
