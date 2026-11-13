<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Excepción para respuestas HTTP controladas (403, 404, 419, etc.).
 */
final class HttpException extends RuntimeException
{
    private int $status;

    public function __construct(int $status, string $message = '')
    {
        $this->status = $status;
        parent::__construct($message !== '' ? $message : self::defaultMessage($status), $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    private static function defaultMessage(int $status): string
    {
        $mensajes = [
            403 => 'No tienes permisos para acceder a este recurso.',
            404 => 'La página que buscas no existe.',
            405 => 'El método HTTP no está permitido.',
            419 => 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.',
            500 => 'Ocurrió un error interno. Inténtalo más tarde.',
        ];

        return $mensajes[$status] ?? 'No fue posible completar la solicitud.';
    }
}
