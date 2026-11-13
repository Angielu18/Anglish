<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Mensajes de una sola vez.
 * Se guardan en la sesión y se consumen en la siguiente vista.
 */
final class Flash
{
    public static function set(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][$tipo][] = $mensaje;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function consume(): array
    {
        $mensajes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return is_array($mensajes) ? $mensajes : [];
    }

    public static function has(string $tipo): bool
    {
        return !empty($_SESSION['_flash'][$tipo]);
    }
}
