<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Protección CSRF (Cross-Site Request Forgery).
 *
 * El token vive en la sesión. Todos los formularios POST lo envían en el
 * campo _token y el front controller lo compara con hash_equals antes de
 * ejecutar cualquier acción.
 */
final class Csrf
{
    private const CLAVE = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::CLAVE]) || !is_string($_SESSION[self::CLAVE])) {
            $_SESSION[self::CLAVE] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CLAVE];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        $guardado = $_SESSION[self::CLAVE] ?? '';

        return is_string($guardado)
            && $guardado !== ''
            && is_string($token)
            && $token !== ''
            && hash_equals($guardado, $token);
    }

    public static function validateOrAbort(): void
    {
        $token = isset($_POST['_token']) ? (string) $_POST['_token'] : null;

        if (!self::validate($token)) {
            Logger::warning('Token CSRF inválido', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
                'ruta' => $_GET['r'] ?? '',
            ]);

            throw new HttpException(419);
        }
    }

    public static function rotate(): void
    {
        $_SESSION[self::CLAVE] = bin2hex(random_bytes(32));
    }
}
