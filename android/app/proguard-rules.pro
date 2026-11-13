# Reglas base para Retrofit + Gson
-keepattributes Signature
-keepattributes *Annotation*
-keep class com.anglish.app.data.remote.dto.** { *; }

# Room no requiere reglas adicionales en versiones recientes.
# Mantener modelos serializados por Gson.
-keepclassmembers,allowobfuscation class * {
    @com.google.gson.annotations.SerializedName <fields>;
}
