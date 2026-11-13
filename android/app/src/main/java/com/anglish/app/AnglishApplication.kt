package com.anglish.app

import android.app.Application
import com.anglish.app.di.AppContainer

/**
 * Punto de composición de la aplicación.
 *
 * Se usa inyección de dependencias manual (sin Hilt) para que la sustentación
 * sea sencilla: todo el grafo de objetos vive en [AppContainer] y cualquier
 * Activity/Fragment lo obtiene desde [container].
 */
class AnglishApplication : Application() {

    lateinit var container: AppContainer
        private set

    override fun onCreate() {
        super.onCreate()
        container = AppContainer(this)
    }
}
