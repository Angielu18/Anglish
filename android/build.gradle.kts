// Plugins declarados a nivel de proyecto; se aplican en app/build.gradle.kts.
//
// AGP 9.5.1 usa Kotlin integrado (built-in Kotlin): ya no se aplica
// org.jetbrains.kotlin.android. Room se procesa con KSP, no con kapt.
plugins {
    id("com.android.application") version "9.5.1" apply false
    id("com.google.devtools.ksp") version "2.3.13" apply false
}
