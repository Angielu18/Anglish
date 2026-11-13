<?php

declare(strict_types=1);

/**
 * Configuración central de Anglish.
 *
 * No se usa ningún framework: este arreglo es leído por el helper config()
 * y por las clases de la carpeta app/core. Los valores sensibles pueden
 * sobreescribirse con variables de entorno sin cambiar el código.
 */
return [
    'app' => [
        'name'        => 'Anglish',
        'subtitle'    => 'Academia de inglés',
        'version'     => '3.0.0',
        'env'         => getenv('APP_ENV') ?: 'local',
        'debug'       => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'timezone'    => 'America/Bogota',
        'locale'      => 'es_CO',
        'moneda'      => 'COP',
        'nota_minima' => 3.0,
        'asistencia_minima' => 80.0,
    ],

    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_DATABASE') ?: 'anglish',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset'  => 'utf8mb4',
    ],

    'session' => [
        'name'     => 'ANGLISH_SESSION',
        'lifetime' => 1800, // 30 minutos de inactividad
        'regenerate_every' => 600,
        'same_site' => 'Lax',
    ],

    'security' => [
        'max_intentos'  => 5,
        'bloqueo_minutos' => 15,
    ],

    'pagination' => [
        'per_page' => 10,
    ],
];
