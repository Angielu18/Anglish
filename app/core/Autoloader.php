<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Autoloader PSR-4 artesanal.
 *
 * El prefijo App\ se traduce a la carpeta app/. Como las carpetas del
 * proyecto están en minúscula (core, controllers, models), el primer
 * segmento se pasa a minúscula. Así funciona igual en Windows y Linux.
 *
 * Ejemplo:
 *   App\Core\Database      -> app/core/Database.php
 *   App\Controllers\AuthController -> app/controllers/AuthController.php
 */
final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    private static function load(string $class): void
    {
        $prefix = 'App\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $parts    = explode('\\', $relative);

        if (count($parts) > 1) {
            $parts[0] = strtolower($parts[0]);
        }

        $file = APP_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';

        if (is_file($file)) {
            require_once $file;
        }
    }
}
