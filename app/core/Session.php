<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Manejo seguro de la sesión PHP.
 *
 * - Cookie HttpOnly y SameSite=Lax.
 * - Secure automático cuando la petición llega por HTTPS.
 * - Cierre por inactividad (30 minutos por defecto).
 * - Regeneración periódica del identificador para reducir fijación de sesión.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = config('session');
        $lifetime = (int) $config['lifetime'];

        session_name((string) $config['name']);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => base_url() !== '' ? base_url() : '/',
            'domain'   => '',
            'secure'   => self::isHttps(),
            'httponly' => true,
            'samesite' => (string) $config['same_site'],
        ]);

        session_start();

        $ahora = time();
        $ultima = isset($_SESSION['_last_activity']) ? (int) $_SESSION['_last_activity'] : null;

        // Sesión vencida por inactividad.
        if ($ultima !== null && ($ahora - $ultima) > $lifetime) {
            $_SESSION = [];
            session_destroy();

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    [
                        'expires'  => time() - 42000,
                        'path'     => $params['path'],
                        'domain'   => $params['domain'],
                        'secure'   => (bool) $params['secure'],
                        'httponly' => (bool) $params['httponly'],
                        'samesite' => (string) $params['samesite'],
                    ]
                );
            }

            session_start();
            session_regenerate_id(true);
            Flash::set('warning', 'Tu sesión expiró por inactividad. Vuelve a iniciar sesión.');
        }

        $_SESSION['_last_activity'] = $ahora;

        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = $ahora;
        }

        $regenerateEvery = (int) $config['regenerate_every'];

        if (($ahora - (int) $_SESSION['_created_at']) >= $regenerateEvery) {
            session_regenerate_id(true);
            $_SESSION['_created_at'] = $ahora;
        }
    }

    /**
     * @param mixed $value
     */
    public static function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_created_at'] = time();
        }
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => (bool) $params['secure'],
                    'httponly' => (bool) $params['httponly'],
                    'samesite' => (string) $params['samesite'],
                ]
            );
        }

        session_name((string) config('session.name'));
    }

    private static function isHttps(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    }
}
