<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Autenticación y autorización basada en roles (RBAC).
 *
 * El rol se guarda en la sesión, pero las decisiones sensibles siempre se
 * validan de nuevo en el servidor. Ocultar un enlace en el menú no es una
 * medida de seguridad.
 */
final class Auth
{
    private const CLAVE_SESION = '_auth_user';

    /**
     * Hash de relleno para que un correo inexistente tarde un tiempo
     * similar a una contraseña incorrecta (mitiga enumeración por tiempo).
     */
    private const HASH_RELLENO = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /**
     * @return array{ok:bool, mensaje:string}
     */
    public static function attempt(string $email, string $password): array
    {
        $email = mb_strtolower(trim($email));
        $userModel = new User();
        $user = $userModel->findForAuth($email);

        if ($user === null) {
            password_verify($password, self::HASH_RELLENO);
            Logger::warning('Intento de acceso con correo inexistente', [
                'email' => $email,
                'ip'    => Request::ip(),
            ]);

            return ['ok' => false, 'mensaje' => self::mensajeGenerico()];
        }

        if ((string) $user['estado'] !== 'ACTIVO') {
            Logger::warning('Intento de acceso con usuario inactivo', [
                'usuario_id' => (int) $user['id'],
                'ip'         => Request::ip(),
            ]);

            return ['ok' => false, 'mensaje' => self::mensajeGenerico()];
        }

        $bloqueadoHasta = $user['bloqueado_hasta'] ?? null;

        if ($bloqueadoHasta !== null && strtotime((string) $bloqueadoHasta) > time()) {
            Logger::warning('Intento de acceso con cuenta bloqueada', [
                'usuario_id' => (int) $user['id'],
                'ip'         => Request::ip(),
            ]);

            return [
                'ok'      => false,
                'mensaje' => 'La cuenta está bloqueada temporalmente. Inténtalo de nuevo en unos minutos.',
            ];
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            $userModel->recordFailedAttempt(
                (int) $user['id'],
                (int) config('security.max_intentos', 5),
                (int) config('security.bloqueo_minutos', 15)
            );

            Logger::warning('Contraseña incorrecta', [
                'usuario_id' => (int) $user['id'],
                'ip'         => Request::ip(),
            ]);

            return ['ok' => false, 'mensaje' => self::mensajeGenerico()];
        }

        // Actualiza el hash si PHP cambió el algoritmo o el costo.
        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            $userModel->updatePasswordHash(
                (int) $user['id'],
                password_hash($password, PASSWORD_DEFAULT)
            );
        }

        $userModel->clearFailedAttempts((int) $user['id']);
        $userModel->touchLogin((int) $user['id']);
        self::login($user);

        Logger::info('Inicio de sesión exitoso', [
            'usuario_id' => (int) $user['id'],
            'rol'        => (string) $user['rol'],
            'ip'         => Request::ip(),
        ]);

        return ['ok' => true, 'mensaje' => 'Bienvenido a Anglish.'];
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function login(array $user): void
    {
        Session::regenerate();

        $_SESSION[self::CLAVE_SESION] = [
            'id'              => (int) $user['id'],
            'nombres'         => (string) $user['nombres'],
            'apellidos'       => (string) $user['apellidos'],
            'nombre_completo' => trim((string) $user['nombres'] . ' ' . (string) $user['apellidos']),
            'email'           => (string) $user['email'],
            'rol'             => strtoupper((string) $user['rol']),
            'rol_id'          => (int) $user['rol_id'],
            'estudiante_id'   => isset($user['estudiante_id']) ? (int) $user['estudiante_id'] : null,
            'profesor_id'     => isset($user['profesor_id']) ? (int) $user['profesor_id'] : null,
            'nivel'           => $user['nivel_codigo'] ?? null,
        ];

        $_SESSION['_last_activity'] = time();
        self::$cache = $_SESSION[self::CLAVE_SESION];
        Csrf::rotate();
    }

    public static function logout(): void
    {
        unset($_SESSION[self::CLAVE_SESION]);
        self::$cache = null;
        Session::regenerate();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $user = $_SESSION[self::CLAVE_SESION] ?? null;
        self::$cache = is_array($user) ? $user : null;

        return self::$cache;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user !== null ? (int) $user['id'] : null;
    }

    public static function role(): ?string
    {
        $user = self::user();

        return $user !== null ? (string) $user['rol'] : null;
    }

    public static function estudianteId(): ?int
    {
        $user = self::user();

        return $user !== null && !empty($user['estudiante_id']) ? (int) $user['estudiante_id'] : null;
    }

    public static function profesorId(): ?int
    {
        $user = self::user();

        return $user !== null && !empty($user['profesor_id']) ? (int) $user['profesor_id'] : null;
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === strtoupper($role);
    }

    public static function homeForRole(string $role): string
    {
        // En esta fase los tres roles comparten la ruta /dashboard, pero el
        // controlador construye datos y vistas diferentes. El método deja
        // preparado el mapa para futuras rutas por rol.
        $mapa = [
            'ADMINISTRADOR' => 'dashboard',
            'PROFESOR'      => 'dashboard',
            'ESTUDIANTE'    => 'dashboard',
        ];

        return $mapa[strtoupper($role)] ?? 'dashboard';
    }

    public static function requireLogin(): void
    {
        if (self::guest()) {
            Flash::set('warning', 'Debes iniciar sesión para continuar.');
            Response::redirect('login');
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            Response::redirect(self::homeForRole((string) self::role()));
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();

        if (!self::hasRole($role)) {
            Logger::warning('Acceso denegado por RBAC', [
                'usuario_id' => self::id(),
                'rol'        => self::role(),
                'requerido'  => strtoupper($role),
                'ruta'       => $_GET['r'] ?? '',
                'ip'         => Request::ip(),
            ]);

            throw new HttpException(403);
        }
    }

    private static function mensajeGenerico(): string
    {
        return 'Correo o contraseña incorrectos. Verifica tus datos e inténtalo de nuevo.';
    }
}
