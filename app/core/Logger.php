<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Registro de eventos técnicos en logs/app-YYYY-MM-DD.log.
 * Cada línea es un JSON para que sea fácil de leer o procesar.
 */
final class Logger
{
    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function exception(Throwable $exception, array $context = []): void
    {
        $context['exception'] = get_class($exception);
        $context['file'] = $exception->getFile();
        $context['line'] = $exception->getLine();

        self::write('ERROR', $exception->getMessage(), $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function write(string $level, string $message, array $context): void
    {
        $directory = ROOT_PATH . '/logs';

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $line = json_encode([
            'fecha'    => date('Y-m-d H:i:s'),
            'nivel'    => $level,
            'mensaje'  => $message,
            'contexto' => $context,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($line === false) {
            return;
        }

        @file_put_contents(
            $directory . '/app-' . date('Y-m-d') . '.log',
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
