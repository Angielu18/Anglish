package com.anglish.app.data.remote

import com.anglish.app.BuildConfig
import com.anglish.app.data.session.SessionManager
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

/**
 * Fábrica de Retrofit.
 *
 * - La URL base se configura en app/build.gradle.kts (BuildConfig.API_BASE_URL).
 * - El logging solo se activa en debug y oculta el header Authorization.
 * - Los timeouts son cortos para que el modo offline se active rápido cuando
 *   el servidor local no está encendido.
 */
object RetrofitClient {

    fun create(sessionManager: SessionManager): AnglishApiService {
        val logging = HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG) {
                HttpLoggingInterceptor.Level.BASIC
            } else {
                HttpLoggingInterceptor.Level.NONE
            }
            redactHeader("Authorization")
        }

        val clienteHttp = OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor { sessionManager.token })
            .addInterceptor(logging)
            .connectTimeout(6, TimeUnit.SECONDS)
            .readTimeout(10, TimeUnit.SECONDS)
            .writeTimeout(10, TimeUnit.SECONDS)
            .retryOnConnectionFailure(true)
            .build()

        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(clienteHttp)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(AnglishApiService::class.java)
    }
}
