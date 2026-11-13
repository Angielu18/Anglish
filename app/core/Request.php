<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Acceso controlado a los datos de la petición HTTP.
 */
final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public static function only(array $keys): array
    {
        $datos = [];

        foreach ($keys as $key) {
            $datos[$key] = self::input($key, '');
        }

        return $datos;
    }

    public static function ip(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
    }

    public static function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'), 0, 255);
    }

    public static function isAjax(): bool
    {
        return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }
}
