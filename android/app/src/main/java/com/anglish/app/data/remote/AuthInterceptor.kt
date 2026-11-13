package com.anglish.app.data.remote

import okhttp3.Interceptor
import okhttp3.Response

/**
 * Adjunta el token Bearer a cada petición.
 *
 * El token vive en SessionManager (EncryptedSharedPreferences) y se entrega
 * mediante una lambda para no acoplar Retrofit a Android.
 */
class AuthInterceptor(
    private val proveedorToken: () -> String?
) : Interceptor {

    override fun intercept(chain: Interceptor.Chain): Response {
        val token = proveedorToken()

        val request = chain.request()
            .newBuilder()
            .header("Accept", "application/json")
            .apply {
                if (!token.isNullOrBlank()) {
                    header("Authorization", "Bearer $token")
                }
            }
            .build()

        return chain.proceed(request)
    }
}
