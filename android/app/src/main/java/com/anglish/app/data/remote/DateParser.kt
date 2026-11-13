package com.anglish.app.data.remote

import java.time.Instant
import java.time.LocalDate
import java.time.LocalDateTime
import java.time.OffsetDateTime
import java.time.ZoneId
import java.time.format.DateTimeFormatter

/**
 * El backend puede enviar fechas en varios formatos según cómo se configure
 * PHP/MySQL. Este parser intenta los más comunes y devuelve null si no puede.
 */
object DateParser {

    private val formatoMysql = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")

    fun aInstant(valor: String?): Instant? {
        if (valor.isNullOrBlank()) return null

        return runCatching { Instant.parse(valor) }
            .recoverCatching { OffsetDateTime.parse(valor).toInstant() }
            .recoverCatching {
                LocalDateTime.parse(valor, formatoMysql)
                    .atZone(ZoneId.systemDefault())
                    .toInstant()
            }
            .recoverCatching {
                LocalDate.parse(valor)
                    .atStartOfDay(ZoneId.systemDefault())
                    .toInstant()
            }
            .getOrNull()
    }
}
