package com.anglish.app.di

import android.content.Context
import com.anglish.app.data.local.AnglishDatabase
import com.anglish.app.data.local.seed.DemoDataSeeder
import com.anglish.app.data.remote.AnglishApiService
import com.anglish.app.data.remote.RetrofitClient
import com.anglish.app.data.repository.DashboardRepository
import com.anglish.app.data.session.SessionManager

/**
 * Contenedor de dependencias de la app.
 *
 * Capas:
 *  - SessionManager  -> token y usuario activo (EncryptedSharedPreferences)
 *  - AnglishDatabase -> Room / SQLite (fuente de verdad local)
 *  - AnglishApiService -> Retrofit (sincronización remota)
 *  - DashboardRepository -> une Room + API para el ViewModel
 */
class AppContainer(context: Context) {

    val sessionManager: SessionManager = SessionManager(context)

    val database: AnglishDatabase = AnglishDatabase.getInstance(context)

    val usuarioDao = database.usuarioDao()
    val cursoDao = database.cursoDao()
    val matriculaDao = database.matriculaDao()
    val asistenciaDao = database.asistenciaDao()
    val evaluacionDao = database.evaluacionDao()

    val api: AnglishApiService = RetrofitClient.create(sessionManager)

    val demoDataSeeder = DemoDataSeeder(
        usuarioDao = usuarioDao,
        cursoDao = cursoDao,
        matriculaDao = matriculaDao,
        asistenciaDao = asistenciaDao,
        evaluacionDao = evaluacionDao
    )

    val dashboardRepository: DashboardRepository = DashboardRepository(
        database = database,
        usuarioDao = usuarioDao,
        cursoDao = cursoDao,
        matriculaDao = matriculaDao,
        asistenciaDao = asistenciaDao,
        evaluacionDao = evaluacionDao,
        api = api,
        sessionManager = sessionManager,
        demoDataSeeder = demoDataSeeder
    )
}
