<?php

declare(strict_types=1);

/**
 * Funciones de apoyo para las vistas y los controladores.
 *
 * Se mantienen deliberadamente pequeñas y sin dependencias externas para
 * que el proyecto se pueda explicar línea por línea en la sustentación.
 */

use App\Core\Csrf;

/**
 * Lee una clave de configuración con notación de punto.
 * Ejemplo: config('db.host') devuelve 127.0.0.1.
 *
 * @param mixed $default
 * @return mixed
 */
function config(string $key, $default = null)
{
    static $cache = null;

    if ($cache === null) {
        $cache = require APP_PATH . '/config/config.php';
    }

    $value = $cache;

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

/**
 * Escapa cualquier valor antes de imprimirlo en HTML.
 * Es la defensa principal contra XSS.
 *
 * @param mixed $value
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Ruta base de la aplicación.
 * En XAMPP normalmente devuelve /Anglish.
 */
function base_url(string $path = ''): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $base   = rtrim(str_replace('\\', '/', dirname($script)), '/');

    if ($base === '.' || $base === '/') {
        $base = '';
    }

    return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

/**
 * Construye una URL interna usando el front controller.
 *
 * @param array<string, scalar> $params
 */
function url(string $route = 'dashboard', array $params = []): string
{
    $query = ['r' => $route] + $params;

    return base_url('index.php') . '?' . http_build_query($query);
}

/**
 * URL de un recurso estático dentro de public/.
 */
function asset(string $path): string
{
    return base_url('public/' . ltrim($path, '/'));
}

/**
 * Campo oculto CSRF reutilizable en formularios.
 */
function csrf_field(): string
{
    return Csrf::field();
}

/**
 * Formatea un valor monetario en pesos colombianos.
 * Se usa punto para miles y coma para decimales.
 */
function money(float $value, bool $decimals = false): string
{
    $formatted = number_format(
        $value,
        $decimals ? 2 : 0,
        ',',
        '.'
    );

    return '$ ' . $formatted;
}

/**
 * Redondea y muestra un porcentaje.
 */
function porcentaje(float $value, int $decimals = 0): string
{
    return number_format($value, $decimals, ',', '.') . ' %';
}

/**
 * Iniciales de un nombre para el avatar.
 */
function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre)) ?: [];
    $partes = array_values(array_filter($partes));

    if (count($partes) === 0) {
        return 'A';
    }

    $primera = mb_substr($partes[0], 0, 1, 'UTF-8');
    $ultima  = count($partes) > 1
        ? mb_substr($partes[count($partes) - 1], 0, 1, 'UTF-8')
        : '';

    return mb_strtoupper($primera . $ultima, 'UTF-8');
}

/**
 * Nombres de los meses en español.
 */
function mes_nombre(int $mes): string
{
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    return $meses[$mes] ?? '';
}

/**
 * Nombres de los días de la semana en español.
 */
function dia_nombre(int $numero): string
{
    $dias = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    return $dias[$numero] ?? '';
}

/**
 * Convierte una fecha MySQL a un objeto DateTimeImmutable.
 */
function fecha_objeto(?string $fecha): ?DateTimeImmutable
{
    if ($fecha === null || trim($fecha) === '') {
        return null;
    }

    try {
        return new DateTimeImmutable($fecha);
    } catch (Exception $e) {
        return null;
    }
}

function fecha_corta(?string $fecha): string
{
    $objeto = fecha_objeto($fecha);

    return $objeto ? $objeto->format('d/m/Y') : '—';
}

function fecha_hora(?string $fecha): string
{
    $objeto = fecha_objeto($fecha);

    return $objeto ? $objeto->format('d/m/Y H:i') : '—';
}

/**
 * Fecha larga en español: "Miércoles, 8 de noviembre de 2026".
 */
function fecha_larga(?string $fecha): string
{
    $objeto = fecha_objeto($fecha);

    if ($objeto === null) {
        return '—';
    }

    return dia_nombre((int) $objeto->format('N'))
        . ', ' . $objeto->format('j')
        . ' de ' . mes_nombre((int) $objeto->format('n'))
        . ' de ' . $objeto->format('Y');
}

/**
 * Hora en formato HH:MM.
 */
function hora_corta(?string $hora): string
{
    return $hora ? substr($hora, 0, 5) : '—';
}

/**
 * Regla de urgencia compartida por evaluaciones y alertas.
 *
 * - vencida: la fecha ya pasó.
 * - hoy: faltan menos de 24 horas.
 * - proxima: faltan entre 24 y 48 horas.
 * - normal: faltan más de 48 horas.
 *
 * @return array{clave:string, etiqueta:string, clase:string, detalle:string, icono:string}
 */
function urgencia_evento(?string $fecha): array
{
    $objeto = fecha_objeto($fecha);

    if ($objeto === null) {
        return [
            'clave'   => 'normal',
            'etiqueta' => 'Programada',
            'clase'   => 'success',
            'detalle' => 'Sin fecha límite',
            'icono'   => 'bi-calendar3',
        ];
    }

    $ahora = new DateTimeImmutable('now');
    $segundos = $objeto->getTimestamp() - $ahora->getTimestamp();
    $horas = $segundos / 3600;

    if ($segundos <= 0) {
        $diasVencida = (int) floor(abs($segundos) / 86400);

        return [
            'clave'   => 'vencida',
            'etiqueta' => 'Vencida',
            'clase'   => 'danger',
            'detalle' => $diasVencida > 0
                ? 'Venció hace ' . $diasVencida . ' día' . ($diasVencida === 1 ? '' : 's')
                : 'Venció hoy',
            'icono'   => 'bi-exclamation-octagon',
        ];
    }

    if ($horas < 24) {
        return [
            'clave'   => 'hoy',
            'etiqueta' => '≤ 24 h',
            'clase'   => 'danger',
            'detalle' => 'Vence hoy',
            'icono'   => 'bi-alarm',
        ];
    }

    if ($horas <= 48) {
        return [
            'clave'   => 'proxima',
            'etiqueta' => '≤ 48 h',
            'clase'   => 'warning',
            'detalle' => 'Vence en ' . (int) ceil($horas) . ' horas',
            'icono'   => 'bi-clock',
        ];
    }

    $dias = (int) floor($horas / 24);

    return [
        'clave'   => 'normal',
        'etiqueta' => 'Programada',
        'clase'   => 'success',
        'detalle' => 'Vence en ' . $dias . ' día' . ($dias === 1 ? '' : 's'),
        'icono'   => 'bi-calendar-check',
    ];
}

/**
 * Urgencia específica de una alerta.
 *
 * Las alertas de rendimiento y asistencia no tienen una fecha límite:
 * se muestran como "Revisar" para no confundirlas con una entrega vencida.
 * Las de evaluación y pago sí usan la fecha del evento.
 *
 * @return array{clave:string, etiqueta:string, clase:string, detalle:string, icono:string}
 */
function urgencia_alerta(string $tipo, ?string $fecha): array
{
    $tipo = strtoupper($tipo);

    if (in_array($tipo, ['RENDIMIENTO', 'ASISTENCIA'], true)) {
        return [
            'clave'    => 'atencion',
            'etiqueta' => 'Revisar',
            'clase'    => 'warning',
            'detalle'  => 'Requiere seguimiento',
            'icono'    => 'bi-eye',
        ];
    }

    return urgencia_evento($fecha);
}

/**
 * Etiqueta y clase visual para el estado de asistencia.
 *
 * @return array{etiqueta:string, clase:string, icono:string}
 */
function estado_asistencia(string $estado): array
{
    $mapa = [
        'PRESENTE' => ['etiqueta' => 'Presente', 'clase' => 'success', 'icono' => 'bi-check-circle'],
        'TARDE'    => ['etiqueta' => 'Tarde', 'clase' => 'warning', 'icono' => 'bi-clock'],
        'AUSENTE'  => ['etiqueta' => 'Ausente', 'clase' => 'danger', 'icono' => 'bi-x-circle'],
        'EXCUSA'   => ['etiqueta' => 'Excusa', 'clase' => 'info', 'icono' => 'bi-file-earmark-medical'],
    ];

    return $mapa[$estado] ?? ['etiqueta' => $estado, 'clase' => 'secondary', 'icono' => 'bi-dash-circle'];
}

/**
 * Traduce el rol a una etiqueta más amable.
 */
function rol_etiqueta(string $rol): string
{
    $mapa = [
        'ADMINISTRADOR' => 'Administrador',
        'PROFESOR'      => 'Profesor',
        'ESTUDIANTE'    => 'Estudiante',
    ];

    return $mapa[$rol] ?? $rol;
}

/**
 * Tipo de evaluación para mostrar.
 */
function tipo_evaluacion(string $tipo): string
{
    $mapa = [
        'TALLER'        => 'Taller',
        'QUIZ'          => 'Quiz',
        'PARCIAL'       => 'Parcial',
        'PROYECTO'      => 'Proyecto',
        'EXAMEN_FINAL'  => 'Examen final',
    ];

    return $mapa[$tipo] ?? $tipo;
}

/**
 * Método de pago para mostrar.
 */
function metodo_pago(string $metodo): string
{
    $mapa = [
        'EFECTIVO'     => 'Efectivo',
        'TRANSFERENCIA' => 'Transferencia',
        'TARJETA'      => 'Tarjeta',
        'NEQUI'        => 'Nequi',
        'DAVIPLATA'    => 'Daviplata',
    ];

    return $mapa[$metodo] ?? $metodo;
}

/**
 * Clase visual de una nota según la escala 1.0 a 5.0.
 */
function nota_clase(float $nota): string
{
    if ($nota >= (float) config('app.nota_minima', 3.0)) {
        return 'success';
    }

    return 'danger';
}

/**
 * Clase visual de un saldo.
 */
function saldo_clase(float $saldo): string
{
    return $saldo > 0 ? 'danger' : 'success';
}

/**
 * Etiqueta del estado de cartera.
 */
function cartera_clase(string $estado): string
{
    return strtoupper($estado) === 'EN MORA' ? 'danger' : (strtoupper($estado) === 'AL DÍA' ? 'success' : 'warning');
}

/**
 * Atajo para saber si la ruta actual coincide con la del menú.
 */
function ruta_activa(string $ruta): bool
{
    $actual = isset($_GET['r']) ? trim((string) $_GET['r'], '/') : 'dashboard';

    return $actual === trim($ruta, '/');
}

/**
 * Fecha actual en español para el encabezado.
 */
function fecha_hoy_larga(): string
{
    return fecha_larga(date('Y-m-d'));
}
