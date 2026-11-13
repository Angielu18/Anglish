<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Respuestas HTTP de la aplicación.
 */
final class Response
{
    /**
     * @param array<string, scalar> $params
     */
    public static function redirect(string $route = 'dashboard', array $params = []): void
    {
        header('Location: ' . url($route, $params));
        exit;
    }

    public static function abort(int $status, string $message = ''): void
    {
        throw new HttpException($status, $message);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
