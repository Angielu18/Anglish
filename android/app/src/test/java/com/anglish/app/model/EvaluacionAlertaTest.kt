package com.anglish.app.model

import org.junit.Assert.assertEquals
import org.junit.Test
import java.time.Duration
import java.time.Instant

/**
 * Pruebas de la regla de urgencia del Centro de Alertas.
 * No requieren emulador ni Android: son JUnit puras.
 */
class EvaluacionAlertaTest {

    private val ahora: Instant = Instant.parse("2026-11-08T12:00:00Z")

    @Test
    fun `una evaluacion vencida se marca como VENCIDA`() {
        val evaluacion = evaluacionConFecha(ahora.minus(Duration.ofHours(1)))

        assertEquals(Urgencia.VENCIDA, evaluacion.urgencia(ahora))
    }

    @Test
    fun `una evaluacion en menos de 24 horas se marca como HOY`() {
        val evaluacion = evaluacionConFecha(ahora.plus(Duration.ofHours(6)))

        assertEquals(Urgencia.HOY, evaluacion.urgencia(ahora))
    }

    @Test
    fun `una evaluacion entre 24 y 48 horas se marca como PROXIMA`() {
        val evaluacion = evaluacionConFecha(ahora.plus(Duration.ofHours(40)))

        assertEquals(Urgencia.PROXIMA, evaluacion.urgencia(ahora))
    }

    @Test
    fun `una evaluacion con mas de 48 horas se marca como NORMAL`() {
        val evaluacion = evaluacionConFecha(ahora.plus(Duration.ofDays(6)))

        assertEquals(Urgencia.NORMAL, evaluacion.urgencia(ahora))
    }

    private fun evaluacionConFecha(fecha: Instant): EvaluacionAlerta =
        EvaluacionAlerta(
            id = 1L,
            cursoNombre = "Inglés A1 — Fundamentos",
            titulo = "Parcial de prueba",
            tipo = TipoEvaluacion.PARCIAL,
            fechaEntrega = fecha,
            porcentaje = 25.0
        )
}
