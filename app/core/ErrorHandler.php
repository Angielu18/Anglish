<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Manejo central de excepciones.
 *
 * En desarrollo muestra el detalle; en producción solo un mensaje genérico.
 * El detalle técnico siempre queda en logs/app-YYYY-MM-DD.log.
 */
final class ErrorHandler
{
    public static function register(bool $debug): void
    {
        set_exception_handler(static function (Throwable $exception) use ($debug): void {
            $status = $exception instanceof HttpException ? $exception->status() : 500;

            if ($exception instanceof HttpException) {
                Logger::warning('Error HTTP controlado', [
                    'status'  => $status,
                    'mensaje' => $exception->getMessage(),
                    'ruta'    => $_GET['r'] ?? '',
                    'ip'      => Request::ip(),
                ]);
            } else {
                Logger::exception($exception, [
                    'ruta' => $_GET['r'] ?? '',
                    'ip'   => Request::ip(),
                ]);
            }

            if (!headers_sent()) {
                http_response_code($status);
            }

            if (Request::isAjax()) {
                Response::json([
                    'ok'      => false,
                    'mensaje' => $status === 500 && !$debug
                        ? 'Ocurrió un error interno.'
                        : $exception->getMessage(),
                ], $status);
            }

            $mensaje = $status === 500 && !$debug
                ? 'Ocurrió un error interno. El equipo técnico ya fue notificado.'
                : $exception->getMessage();

            $layout = Auth::check() ? 'app' : 'auth';

            try {
                View::render('errors/error', [
                    'title'     => 'Error ' . $status,
                    'status'    => $status,
                    'message'   => $mensaje,
                    'debug'     => $debug,
                    'exception' => $exception,
                    'user'      => Auth::user(),
                ], $layout);
            } catch (Throwable $inner) {
                echo '<h1>Error ' . (int) $status . '</h1>';
                echo '<p>' . e($mensaje) . '</p>';
            }
        });
    }
}
