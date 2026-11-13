package com.anglish.app.data.remote

import com.anglish.app.data.remote.dto.AsistenciaDto
import com.anglish.app.data.remote.dto.DashboardDto
import com.anglish.app.data.remote.dto.EvaluacionDto
import com.anglish.app.data.remote.dto.LoginRequestDto
import com.anglish.app.data.remote.dto.LoginResponseDto
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

/**
 * Contrato REST de Anglish.
 *
 * Endpoints del backend PHP que se conectarán al mismo esquema MySQL:
 *   POST /auth/login
 *   GET  /estudiantes/{id}/dashboard
 *   GET  /estudiantes/{id}/evaluaciones
 *   GET  /estudiantes/{id}/asistencias
 *
 * Si la API aún no está disponible, el repositorio conserva los datos de Room
 * y la app funciona en modo offline.
 */
interface AnglishApiService {

    @POST("auth/login")
    suspend fun login(@Body request: LoginRequestDto): LoginResponseDto

    @GET("estudiantes/{id}/dashboard")
    suspend fun obtenerDashboard(@Path("id") estudianteId: Long): DashboardDto

    @GET("estudiantes/{id}/evaluaciones")
    suspend fun obtenerEvaluaciones(@Path("id") estudianteId: Long): List<EvaluacionDto>

    @GET("estudiantes/{id}/asistencias")
    suspend fun obtenerAsistencias(@Path("id") estudianteId: Long): List<AsistenciaDto>
}
