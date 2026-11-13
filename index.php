<?php

declare(strict_types=1);

/**
 * Front Controller de Anglish.
 *
 * Todas las peticiones internas llegan aquí. El archivo solo compone el
 * núcleo, inicia la sesión, valida CSRF y despacha la ruta; la lógica vive
 * en controladores y modelos.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

Session::start();

// Cabeceras defensivas básicas.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$router = new Router();

// 1. Módulo Público / Visitantes (Acceso libre sin autenticación)
$router->get('', 'PublicController@index');
$router->get('landing', 'PublicController@index');
$router->post('public/entrevista', 'PublicController@solicitarEntrevista');

// 2. Autenticación Formal por Rol
$router->get('login', 'AuthController@showLogin', ['guest']);
$router->post('login', 'AuthController@login', ['guest']);
$router->post('logout', 'AuthController@logout', ['auth']);

// 3. Dashboard Central por Rol
$router->get('dashboard', 'DashboardController@index', ['auth']);
$router->post('alerts/read', 'DashboardController@markAlertsRead', ['auth']);

// 4. Módulo de Estudiantes
$router->get('estudiante/trabajos', 'EstudianteController@trabajos', ['auth', 'role:ESTUDIANTE']);
$router->post('estudiante/entregar', 'EstudianteController@entregar', ['auth', 'role:ESTUDIANTE']);
$router->get('estudiante/reportes', 'EstudianteController@reportes', ['auth', 'role:ESTUDIANTE']);

// 5. Módulo de Profesores
$router->get('profesor/revisar', 'ProfesorController@revisar', ['auth', 'role:PROFESOR']);
$router->post('profesor/calificar', 'ProfesorController@calificar', ['auth', 'role:PROFESOR']);
$router->get('profesor/tareas', 'ProfesorController@tareas', ['auth', 'role:PROFESOR']);
$router->post('profesor/tareas/crear', 'ProfesorController@crearTarea', ['auth', 'role:PROFESOR']);
$router->get('profesor/reportes', 'ProfesorController@reportes', ['auth', 'role:PROFESOR']);

// 6. Módulo de Administrador
$router->get('admin/entrevistas', 'AdminController@entrevistas', ['auth', 'role:ADMINISTRADOR']);
$router->post('admin/entrevistas/crear', 'AdminController@crearEntrevista', ['auth', 'role:ADMINISTRADOR']);
$router->post('admin/entrevistas/actualizar', 'AdminController@actualizarEntrevista', ['auth', 'role:ADMINISTRADOR']);
$router->get('admin/usuarios', 'AdminController@usuarios', ['auth', 'role:ADMINISTRADOR']);
$router->post('admin/usuarios/crear', 'AdminController@crearUsuario', ['auth', 'role:ADMINISTRADOR']);
$router->post('admin/usuarios/actualizar', 'AdminController@actualizarUsuario', ['auth', 'role:ADMINISTRADOR']);

// 7. Rutas de API REST para la aplicación Android
$router->post('api/v1/auth/login', 'ApiController@login');
$router->get('api/v1/estudiantes/dashboard', 'ApiController@studentDashboard');

$route = isset($_GET['r']) && is_string($_GET['r']) ? $_GET['r'] : '';
$isApi = str_starts_with($route, 'api/');

// Todo POST web exige un token CSRF válido antes de ejecutar el controlador.
if (Request::isPost() && !$isApi) {
    Csrf::validateOrAbort();
}

$router->dispatch(Request::method(), $route);
