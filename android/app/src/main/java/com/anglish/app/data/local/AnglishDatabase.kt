package com.anglish.app.data.local

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase
import androidx.room.TypeConverters
import com.anglish.app.data.local.dao.AsistenciaDao
import com.anglish.app.data.local.dao.CursoDao
import com.anglish.app.data.local.dao.EvaluacionDao
import com.anglish.app.data.local.dao.MatriculaDao
import com.anglish.app.data.local.dao.UsuarioDao
import com.anglish.app.data.local.entity.AsistenciaEntity
import com.anglish.app.data.local.entity.CursoEntity
import com.anglish.app.data.local.entity.EvaluacionEntity
import com.anglish.app.data.local.entity.MatriculaEntity
import com.anglish.app.data.local.entity.UsuarioEntity

/**
 * Base de datos SQLite local (Room).
 *
 * Decisión offline-first: Room es la única fuente de verdad para la UI.
 * Retrofit solo escribe en Room; el Fragment nunca lee la API directamente.
 * Así el Dashboard sigue funcionando sin conexión.
 */
@Database(
    entities = [
        UsuarioEntity::class,
        CursoEntity::class,
        MatriculaEntity::class,
        AsistenciaEntity::class,
        EvaluacionEntity::class
    ],
    version = 1,
    exportSchema = true
)
@TypeConverters(Converters::class)
abstract class AnglishDatabase : RoomDatabase() {

    abstract fun usuarioDao(): UsuarioDao
    abstract fun cursoDao(): CursoDao
    abstract fun matriculaDao(): MatriculaDao
    abstract fun asistenciaDao(): AsistenciaDao
    abstract fun evaluacionDao(): EvaluacionDao

    companion object {
        private const val NOMBRE_BD = "anglish.db"

        @Volatile
        private var INSTANCIA: AnglishDatabase? = null

        fun getInstance(context: Context): AnglishDatabase =
            INSTANCIA ?: synchronized(this) {
                INSTANCIA ?: Room.databaseBuilder(
                    context.applicationContext,
                    AnglishDatabase::class.java,
                    NOMBRE_BD
                )
                    .setJournalMode(JournalMode.WRITE_AHEAD_LOGGING)
                    // En esta entrega la BD es un caché: si cambia el esquema se
                    // reconstruye. En producción se deben escribir migraciones.
                    .fallbackToDestructiveMigration(dropAllTables = true)
                    .build()
                    .also { INSTANCIA = it }
            }
    }
}
