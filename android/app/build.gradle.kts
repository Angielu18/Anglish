plugins {
    id("com.android.application")
    id("com.google.devtools.ksp")
}

android {
    namespace = "com.anglish.app"
    compileSdk = 37

    defaultConfig {
        applicationId = "com.anglish.app"
        minSdk = 26
        targetSdk = 37
        versionCode = 1
        versionName = "1.0.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"

        // URL configurable sin tocar el código:
        //   gradlew assembleDebug -PANGLISH_API_URL="http://10.0.2.2/Anglish/api/v1/"
        // 10.0.2.2 es el localhost del PC visto desde el emulador Android.
        val apiBaseUrl = (project.findProperty("ANGLISH_API_URL") as String?)
            ?: "http://10.0.2.2:8080/api/v1/"
        buildConfigField("String", "API_BASE_URL", "\"$apiBaseUrl\"")
    }

    buildTypes {
        debug {
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
            isMinifyEnabled = false
        }
        release {
            isMinifyEnabled = false // Activar R8 cuando la app esté probada en dispositivo.
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    buildFeatures {
        viewBinding = true
        buildConfig = true
    }

    packaging {
        resources {
            excludes += "/META-INF/{AL2.0,LGPL2.1}"
        }
    }
}

// Room exporta el esquema para poder crear migraciones en las siguientes versiones.
ksp {
    arg("room.schemaLocation", "$projectDir/schemas")
}

dependencies {
    // Núcleo Android y UI
    implementation("androidx.core:core-ktx:1.15.0")
    implementation("androidx.appcompat:appcompat:1.7.0")
    implementation("com.google.android.material:material:1.12.0")
    implementation("androidx.constraintlayout:constraintlayout:2.2.0")
    implementation("androidx.activity:activity-ktx:1.9.3")
    implementation("androidx.fragment:fragment-ktx:1.8.5")
    implementation("androidx.recyclerview:recyclerview:1.3.2")
    implementation("androidx.swiperefreshlayout:swiperefreshlayout:1.1.0")

    // Ciclo de vida, ViewModel y Navigation (MVVM)
    implementation("androidx.lifecycle:lifecycle-viewmodel-ktx:2.8.7")
    implementation("androidx.lifecycle:lifecycle-livedata-ktx:2.8.7")
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.8.7")
    implementation("androidx.lifecycle:lifecycle-viewmodel-savedstate:2.8.7")
    implementation("androidx.navigation:navigation-fragment-ktx:2.8.5")
    implementation("androidx.navigation:navigation-ui-ktx:2.8.5")

    // Persistencia local: Room + SQLite
    implementation("androidx.room:room-runtime:2.8.5")
    implementation("androidx.room:room-ktx:2.8.5")
    ksp("androidx.room:room-compiler:2.8.5")

    // Red: Retrofit + Gson + OkHttp
    implementation("com.squareup.retrofit2:retrofit:2.11.0")
    implementation("com.squareup.retrofit2:converter-gson:2.11.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    // Corrutinas y estado
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.9.0")

    // Sesión cifrada (token + datos mínimos del usuario)
    implementation("androidx.security:security-crypto:1.1.0")

    // Pruebas unitarias
    testImplementation("junit:junit:4.13.2")
    testImplementation("org.jetbrains.kotlinx:kotlinx-coroutines-test:1.9.0")
    testImplementation("androidx.arch.core:core-testing:2.2.0")

    // Pruebas instrumentadas
    androidTestImplementation("androidx.test.ext:junit:1.2.1")
    androidTestImplementation("androidx.test.espresso:espresso-core:3.6.1")
    androidTestImplementation("androidx.room:room-testing:2.8.5")
}
