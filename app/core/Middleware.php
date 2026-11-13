<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Middleware de rutas. Se evalúa antes del controlador.
 *
 * Formatos admitidos:
 *  - auth
 *  - guest
 *  - role:ADMINISTRADOR
 */
final class Middleware
{
    public static function handle(string $name): void
    {
        if ($name === 'auth') {
            Auth::requireLogin();
            return;
        }

        if ($name === 'guest') {
            Auth::requireGuest();
            return;
        }

        if (str_starts_with($name, 'role:')) {
            $role = substr($name, 5);
            Auth::requireRole($role);
            return;
        }

        throw new HttpException(500, 'Middleware no reconocido: ' . $name);
    }
}
