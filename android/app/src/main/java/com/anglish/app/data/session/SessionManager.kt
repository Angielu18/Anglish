package com.anglish.app.data.session

import android.content.Context
import android.content.SharedPreferences
import androidx.core.content.edit
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import com.anglish.app.model.NivelIngles
import com.anglish.app.model.Rol

/**
 * Guarda la sesión del usuario en EncryptedSharedPreferences.
 *
 * ¿Por qué cifrado?
 * - El token de acceso y el rol son datos sensibles; no deben quedar en texto
 *   plano dentro de /data/data/<paquete>/shared_prefs.
 * - EncryptedSharedPreferences usa una clave maestra del Android Keystore.
 *
 * Para esta primera entrega la sesión se crea con un estudiante de demostración.
 * Cuando se implemente el Login, basta con llamar a [guardarSesion] después de
 * validar las credenciales contra la API.
 */
@Suppress("DEPRECATION") // Jetpack Security sigue siendo válido para el alcance académico.
class SessionManager(context: Context) {

    private val prefs: SharedPreferences

    init {
        val masterKey = MasterKey.Builder(context)
            .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
            .build()

        prefs = EncryptedSharedPreferences.create(
            context,
            ARCHIVO_PREFERENCIAS,
            masterKey,
            EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
            EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
        )
    }

    val usuarioId: Long?
        get() = prefs.getLong(CLAVE_USUARIO_ID, 0L).takeIf { it > 0L }

    val nombres: String
        get() = prefs.getString(CLAVE_NOMBRES, "").orEmpty()

    val apellidos: String
        get() = prefs.getString(CLAVE_APELLIDOS, "").orEmpty()

    val email: String
        get() = prefs.getString(CLAVE_EMAIL, "").orEmpty()

    val rol: Rol
        get() = Rol.from(prefs.getString(CLAVE_ROL, null))

    val nivelActual: NivelIngles
        get() = NivelIngles.from(prefs.getString(CLAVE_NIVEL, null))

    val token: String?
        get() = prefs.getString(CLAVE_TOKEN, null)?.takeIf { it.isNotBlank() }

    val estaAutenticado: Boolean
        get() = usuarioId != null && token != null

    /**
     * Crea una sesión local de demostración para poder abrir el Dashboard
     * antes de implementar la pantalla de Login.
     *
     * IMPORTANTE: es solo para la entrega académica; en producción esta sesión
     * debe venir del endpoint /auth/login y de un token firmado por el servidor.
     */
    fun asegurarSesionDemo() {
        if (usuarioId != null) return

        guardarSesion(
            usuarioId = 1L,
            nombres = "Sara",
            apellidos = "Gómez",
            email = "sara.gomez@anglish.edu.co",
            rol = Rol.ESTUDIANTE,
            nivel = NivelIngles.A1,
            token = "demo-token-sara-gomez"
        )
    }

    fun guardarSesion(
        usuarioId: Long,
        nombres: String,
        apellidos: String,
        email: String,
        rol: Rol,
        nivel: NivelIngles,
        token: String
    ) {
        prefs.edit {
            putLong(CLAVE_USUARIO_ID, usuarioId)
            putString(CLAVE_NOMBRES, nombres)
            putString(CLAVE_APELLIDOS, apellidos)
            putString(CLAVE_EMAIL, email)
            putString(CLAVE_ROL, rol.name)
            putString(CLAVE_NIVEL, nivel.name)
            putString(CLAVE_TOKEN, token)
        }
    }

    fun actualizarNivel(nivel: NivelIngles) {
        prefs.edit { putString(CLAVE_NIVEL, nivel.name) }
    }

    fun cerrarSesion() {
        prefs.edit { clear() }
    }

    private companion object {
        const val ARCHIVO_PREFERENCIAS = "anglish_sesion_segura"
        const val CLAVE_USUARIO_ID = "usuario_id"
        const val CLAVE_NOMBRES = "nombres"
        const val CLAVE_APELLIDOS = "apellidos"
        const val CLAVE_EMAIL = "email"
        const val CLAVE_ROL = "rol"
        const val CLAVE_NIVEL = "nivel"
        const val CLAVE_TOKEN = "token"
    }
}
