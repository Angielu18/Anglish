<?php

declare(strict_types=1);

/**
 * Pruebas de Anglish sin PHPUnit.
 *
 * Se ejecutan con:
 *   php tests/run_tests.php
 *
 * Incluyen reglas puras (fechas, moneda, validación) y una verificación
 * de integración contra MySQL cuando la base está disponible.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Validator;
use App\Models\Dashboard;

$total = 0;
$fallos = 0;
$omitidas = 0;

function test(string $nombre, callable $callback): void
{
    global $total, $fallos;

    $total++;

    try {
        $callback();
        echo "[OK]    " . $nombre . PHP_EOL;
    } catch (Throwable $e) {
        $fallos++;
        echo "[FALLO] " . $nombre . PHP_EOL;
        echo "        " . $e->getMessage() . PHP_EOL;
    }
}

function assertSameValue($esperado, $real, string $mensaje = ''): void
{
    if ($esperado !== $real) {
        throw new RuntimeException(
            ($mensaje !== '' ? $mensaje . ' ' : '')
            . 'Esperado: ' . var_export($esperado, true)
            . ' / Real: ' . var_export($real, true)
        );
    }
}

function assertTrueValue(bool $condicion, string $mensaje = 'La condición es falsa.'): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

function fechaRelativa(string $modificador): string
{
    return (new DateTimeImmutable('now'))->modify($modificador)->format('Y-m-d H:i:s');
}

echo PHP_EOL . '== Reglas de presentación ==' . PHP_EOL;

test('money formatea pesos colombianos', function (): void {
    assertSameValue('$ 1.200.000', money(1200000));
    assertSameValue('$ 1.200.000,50', money(1200000.5, true));
});

test('porcentaje usa coma decimal', function (): void {
    assertSameValue('60,0 %', porcentaje(60.0, 1));
});

test('iniciales toma nombre y apellido', function (): void {
    assertSameValue('SG', iniciales('Sara Gómez'));
    assertSameValue('A', iniciales('Anglish'));
});

test('fecha larga en español', function (): void {
    assertSameValue('Lunes, 9 de noviembre de 2026', fecha_larga('2026-11-09'));
});

test('rol y tipo de evaluación se traducen', function (): void {
    assertSameValue('Administrador', rol_etiqueta('ADMINISTRADOR'));
    assertSameValue('Examen final', tipo_evaluacion('EXAMEN_FINAL'));
});

echo PHP_EOL . '== Reglas de urgencia ==' . PHP_EOL;

test('una entrega en menos de 24 horas es HOY', function (): void {
    assertSameValue('hoy', urgencia_evento(fechaRelativa('+2 hours'))['clave']);
});

test('una entrega entre 24 y 48 horas es PROXIMA', function (): void {
    assertSameValue('proxima', urgencia_evento(fechaRelativa('+36 hours'))['clave']);
});

test('una entrega de más de 48 horas es NORMAL', function (): void {
    assertSameValue('normal', urgencia_evento(fechaRelativa('+72 hours'))['clave']);
});

test('una fecha pasada es VENCIDA', function (): void {
    assertSameValue('vencida', urgencia_evento(fechaRelativa('-2 hours'))['clave']);
});

test('las alertas de rendimiento se muestran como Revisar', function (): void {
    $urgencia = urgencia_alerta('RENDIMIENTO', fechaRelativa('-2 days'));
    assertSameValue('Revisar', $urgencia['etiqueta']);
    assertSameValue('warning', $urgencia['clase']);
});

echo PHP_EOL . '== Validación del servidor ==' . PHP_EOL;

test('el correo vacío es rechazado', function (): void {
    $errores = Validator::validate(
        ['email' => '', 'password' => ''],
        ['email' => 'required|email', 'password' => 'required|min:8']
    );

    assertTrueValue(isset($errores['email'], $errores['password']));
});

test('un correo inválido es rechazado', function (): void {
    $errores = Validator::validate(
        ['email' => 'correo-sin-arroba'],
        ['email' => 'required|email']
    );

    assertTrueValue(isset($errores['email']));
});

test('una contraseña corta es rechazada', function (): void {
    $errores = Validator::validate(
        ['password' => 'corta'],
        ['password' => 'required|min:8']
    );

    assertTrueValue(isset($errores['password']));
});

test('datos válidos no producen errores', function (): void {
    $errores = Validator::validate(
        ['email' => 'sara.gomez@anglish.edu.co', 'password' => 'Estudiante123*'],
        ['email' => 'required|email', 'password' => 'required|min:8']
    );

    assertSameValue([], $errores);
});

test('Auth conoce el panel de cada rol', function (): void {
    assertSameValue('dashboard', App\Core\Auth::homeForRole('ADMINISTRADOR'));
    assertSameValue('dashboard', App\Core\Auth::homeForRole('PROFESOR'));
    assertSameValue('dashboard', App\Core\Auth::homeForRole('ESTUDIANTE'));
});

echo PHP_EOL . '== Integración con MySQL ==' . PHP_EOL;

$dbDisponible = true;

try {
    $db = Database::connection();
} catch (Throwable $e) {
    $dbDisponible = false;
    $omitidas++;
    echo "[OMITIDA] No se pudo conectar a MySQL: " . $e->getMessage() . PHP_EOL;
}

if ($dbDisponible) {
    test('el esquema tiene 22 tablas y 7 vistas', function () use ($db): void {
        $tablas = (int) $db->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'"
        )->fetchColumn();

        $vistas = (int) $db->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_type = 'VIEW'"
        )->fetchColumn();

        assertSameValue(22, $tablas);
        assertSameValue(7, $vistas);
    });

    test('el hash de Sara corresponde a Estudiante123*', function () use ($db): void {
        $hash = (string) $db->query(
            "SELECT password_hash FROM usuarios WHERE email = 'sara.gomez@anglish.edu.co'"
        )->fetchColumn();

        assertTrueValue($hash !== '', 'No existe el usuario de prueba.');
        assertTrueValue(password_verify('Estudiante123*', $hash));
    });

    test('los indicadores de Sara coinciden con la semilla', function () use ($db): void {
        $progreso = $db->query(
            "SELECT porcentaje_avance, promedio_ponderado
             FROM vw_progreso_estudiante WHERE matricula_id = 1"
        )->fetch();

        $asistencia = $db->query(
            "SELECT porcentaje_asistencia
             FROM vw_asistencia_estudiante WHERE matricula_id = 1"
        )->fetch();

        $cuenta = $db->query(
            "SELECT saldo, estado_cartera
             FROM vw_estado_cuenta WHERE matricula_id = 1"
        )->fetch();

        assertSameValue(45.0, (float) $progreso['porcentaje_avance']);
        assertSameValue(2.53, (float) $progreso['promedio_ponderado']);
        assertSameValue(60.0, (float) $asistencia['porcentaje_asistencia']);
        assertSameValue(900000.0, (float) $cuenta['saldo']);
        assertSameValue('EN MORA', (string) $cuenta['estado_cartera']);
    });

    test('el modelo del estudiante arma el tablero', function (): void {
        $_SESSION['_auth_user'] = [
            'id' => 6,
            'nombres' => 'Sara',
            'apellidos' => 'Gómez',
            'nombre_completo' => 'Sara Gómez',
            'email' => 'sara.gomez@anglish.edu.co',
            'rol' => 'ESTUDIANTE',
            'rol_id' => 3,
            'estudiante_id' => 1,
            'profesor_id' => null,
            'nivel' => 'A1',
        ];

        $tablero = (new Dashboard())->student(1);

        assertSameValue(45.0, $tablero['resumen']['progreso']);
        assertSameValue(60.0, $tablero['resumen']['asistencia']);
        assertSameValue(5, $tablero['resumen']['alertas_sin_leer']);
        assertSameValue('Inglés A1 — Fundamentos', $tablero['matricula']['curso']);
        assertTrueValue(count($tablero['evaluaciones']) >= 1);
    });

    test('el tablero administrativo suma la cartera vencida', function (): void {
        $tablero = (new Dashboard())->admin();

        assertSameValue(12, $tablero['conteos']['estudiantes']);
        assertSameValue(5, $tablero['conteos']['cursos']);
        assertTrueValue($tablero['cartera_total'] > 0);
        assertTrueValue($tablero['recaudo_mes'] > 0);
    });

    test('el tablero del profesor filtra sus cursos', function (): void {
        $tablero = (new Dashboard())->professor(1);

        assertSameValue(2, $tablero['stats']['cursos']);
        assertSameValue(6, $tablero['stats']['estudiantes']);
        assertTrueValue(count($tablero['riesgo']) >= 1);
    });

    test('el módulo de estudiantes lista tareas y entregas', function (): void {
        $tareas = (new \App\Models\Tarea())->listarPorEstudiante(1);
        assertTrueValue(count($tareas) >= 3);
    });

    test('el módulo de profesores lista entregas para revisión', function (): void {
        $entregas = (new \App\Models\Tarea())->listarEntregasParaProfesor(1);
        assertTrueValue(count($entregas) >= 1);
    });

    test('el módulo de administrador lista entrevistas y usuarios', function (): void {
        $entrevistas = (new \App\Models\Entrevista())->listarTodas();
        $usuarios = (new \App\Models\User())->listarTodos();

        assertTrueValue(count($entrevistas) >= 4);
        assertTrueValue(count($usuarios) >= 17);
    });
}

echo PHP_EOL;
echo 'Resultado: ' . ($total - $fallos) . ' correctas, ' . $fallos . ' fallidas, ' . $omitidas . ' omitidas.' . PHP_EOL;

exit($fallos === 0 ? 0 : 1);
