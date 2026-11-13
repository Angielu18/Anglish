<?php

declare(strict_types=1);

/**
 * Arranque de la aplicación.
 *
 * Este archivo NO inicia la sesión ni atiende peticiones: solo prepara
 * constantes, configuración, autocarga, zona horaria y manejo de errores.
 * El front controller (index.php) decide cuándo iniciar la sesión y qué
 * ruta ejecutar. Así las pruebas pueden cargar el núcleo sin cookies.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

require_once APP_PATH . '/core/helpers.php';
require_once APP_PATH . '/core/Autoloader.php';

App\Core\Autoloader::register();

date_default_timezone_set((string) config('app.timezone', 'America/Bogota'));

App\Core\ErrorHandler::register((bool) config('app.debug', false));
