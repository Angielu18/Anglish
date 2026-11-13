package com.anglish.app.ui.common

import android.content.Context
import com.anglish.app.R
import java.time.Instant
import java.time.LocalDate
import java.time.ZoneId
import java.time.format.DateTimeFormatter
import java.util.Locale

/**
 * Formatos de fecha compartidos por los adaptadores.
 * Se usa java.time (API 26+) y el locale español de Colombia.
 */
private val LOCALE_ES_CO: Locale = Locale.forLanguageTag("es-CO")
private val FORMATO_FECHA_HORA = DateTimeFormatter.ofPattern("dd MMM · HH:mm", LOCALE_ES_CO)
private val FORMATO_HORA = DateTimeFormatter.ofPattern("HH:mm", LOCALE_ES_CO)
private val FORMATO_FECHA_CORTA = DateTimeFormatter.ofPattern("dd MMM", LOCALE_ES_CO)
private val FORMATO_FECHA_LARGA = DateTimeFormatter.ofPattern("EEEE, d 'de' MMMM", LOCALE_ES_CO)

fun formatearFechaLarga(context: Context, instante: Instant): String {
    val zona = ZoneId.systemDefault()
    return FORMATO_FECHA_LARGA.format(instante.atZone(zona)).capitalizar()
}

fun formatearFechaEntrega(context: Context, instante: Instant): String {
    val zona = ZoneId.systemDefault()
    val fecha = instante.atZone(zona).toLocalDate()
    val hoy = LocalDate.now(zona)

    return when (fecha) {
        hoy -> context.getString(R.string.date_today, FORMATO_HORA.format(instante.atZone(zona)))
        hoy.plusDays(1) -> context.getString(
            R.string.date_tomorrow,
            FORMATO_HORA.format(instante.atZone(zona))
        )
        else -> FORMATO_FECHA_HORA.format(instante.atZone(zona)).capitalizar()
    }
}

fun formatearFechaCorta(context: Context, instante: Instant): String {
    val zona = ZoneId.systemDefault()
    val fecha = instante.atZone(zona).toLocalDate()
    val hoy = LocalDate.now(zona)

    return when (fecha) {
        hoy -> context.getString(R.string.date_label_today)
        hoy.minusDays(1) -> context.getString(R.string.date_label_yesterday)
        else -> FORMATO_FECHA_CORTA.format(instante.atZone(zona)).capitalizar()
    }
}

private fun String.capitalizar(): String =
    replaceFirstChar { caracter ->
        if (caracter.isLowerCase()) caracter.titlecase(Locale.getDefault()) else caracter.toString()
    }
