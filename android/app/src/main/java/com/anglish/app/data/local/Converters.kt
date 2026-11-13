package com.anglish.app.data.local

import androidx.room.TypeConverter
import com.anglish.app.model.EstadoAsistencia
import com.anglish.app.model.NivelIngles
import com.anglish.app.model.Rol
import com.anglish.app.model.TipoEvaluacion

/**
 * Room no persiste enums por sí solo. Se guardan como texto legible
 * (PRESENTE, A1, PARCIAL...) para que las consultas SQL sean claras.
 */
class Converters {

    @TypeConverter
    fun deRol(valor: Rol?): String? = valor?.name

    @TypeConverter
    fun aRol(valor: String?): Rol? = valor?.let { Rol.from(it) }

    @TypeConverter
    fun deNivel(valor: NivelIngles?): String? = valor?.name

    @TypeConverter
    fun aNivel(valor: String?): NivelIngles? = valor?.let { NivelIngles.from(it) }

    @TypeConverter
    fun deEstadoAsistencia(valor: EstadoAsistencia?): String? = valor?.name

    @TypeConverter
    fun aEstadoAsistencia(valor: String?): EstadoAsistencia? =
        valor?.let { EstadoAsistencia.from(it) }

    @TypeConverter
    fun deTipoEvaluacion(valor: TipoEvaluacion?): String? = valor?.name

    @TypeConverter
    fun aTipoEvaluacion(valor: String?): TipoEvaluacion? = valor?.let { TipoEvaluacion.from(it) }
}
