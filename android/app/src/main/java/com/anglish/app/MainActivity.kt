package com.anglish.app

import android.graphics.Color
import android.os.Bundle
import androidx.activity.SystemBarStyle
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import com.anglish.app.databinding.ActivityMainBinding

/**
 * Activity única que hospeda el NavHostFragment.
 *
 * Se usa Navigation Component para que los siguientes módulos (Login,
 * Asistencia, Alertas) se agreguen como destinations sin duplicar Activities.
 *
 * targetSdk 37 exige edge-to-edge: se pinta detrás de las barras y se aplican
 * los insets al contenedor para que ningún texto quede debajo del sistema.
 */
class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        enableEdgeToEdge(
            statusBarStyle = SystemBarStyle.light(Color.TRANSPARENT, Color.TRANSPARENT),
            navigationBarStyle = SystemBarStyle.light(Color.TRANSPARENT, Color.TRANSPARENT)
        )

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { vista, insets ->
            val barras = insets.getInsets(
                WindowInsetsCompat.Type.systemBars() or WindowInsetsCompat.Type.displayCutout()
            )
            vista.updatePadding(
                left = barras.left,
                top = barras.top,
                right = barras.right,
                bottom = barras.bottom
            )
            insets
        }
    }
}
