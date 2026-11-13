<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Conexión PDO única (patrón singleton).
 *
 * Decisiones:
 *  - ERRMODE_EXCEPTION: un error SQL se convierte en excepción y nunca se
 *    ignora silenciosamente.
 *  - EMULATE_PREPARES = false: MySQL recibe sentencias preparadas reales,
 *    lo que reduce el riesgo de inyección SQL.
 *  - FETCH_ASSOC: los modelos reciben arreglos asociativos legibles.
 */
final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = config('db');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);

            // La base usa utf8mb4 y la zona horaria de Colombia.
            self::$connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            self::$connection->exec("SET time_zone = '-05:00'");
        } catch (PDOException $e) {
            Logger::error('No fue posible conectar a MySQL', [
                'code' => $e->getCode(),
            ]);

            // Mensaje genérico: no se exponen usuario, clave ni DSN.
            throw new RuntimeException(
                'No fue posible conectar con la base de datos. Verifica XAMPP y la configuración.',
                0,
                $e
            );
        }

        return self::$connection;
    }

    /**
     * Solo se usa en pruebas para reiniciar la conexión.
     */
    public static function reset(): void
    {
        self::$connection = null;
    }
}
