package com.anglish.app.data.remote.dto

import com.google.gson.annotations.SerializedName

/**
 * Contrato previsto para el módulo de Login (siguiente incremento).
 * La pantalla de autenticación reutilizará estos DTOs.
 */
data class LoginRequestDto(
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String
)

data class LoginResponseDto(
    @SerializedName("token") val token: String? = null,
    @SerializedName("usuario") val usuario: EstudianteDto? = null
)
