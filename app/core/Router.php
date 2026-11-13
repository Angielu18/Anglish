<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Router mínimo para el front controller.
 *
 * Las rutas se identifican por método HTTP y ruta lógica, por ejemplo
 * GET login o POST login. No se usan parámetros dinámicos en esta fase;
 * se pueden agregar después sin cambiar el front controller.
 */
final class Router
{
    /**
     * @var array<string, array<string, array{handler: mixed, middleware: array<int, string>}>>
     */
    private array $routes = [];

    /**
     * @param mixed $handler
     * @param array<int, string> $middleware
     */
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /**
     * @param mixed $handler
     * @param array<int, string> $middleware
     */
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /**
     * @param mixed $handler
     * @param array<int, string> $middleware
     */
    private function add(string $method, string $path, $handler, array $middleware): void
    {
        $this->routes[$method][self::normalize($path)] = [
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path   = self::normalize($path);

        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            foreach (array_keys($this->routes) as $registeredMethod) {
                if (isset($this->routes[$registeredMethod][$path])) {
                    throw new HttpException(405);
                }
            }

            throw new HttpException(404);
        }

        foreach ($route['middleware'] as $middleware) {
            Middleware::handle($middleware);
        }

        $this->call($route['handler']);
    }

    /**
     * @param mixed $handler
     */
    private function call($handler): void
    {
        if ($handler instanceof \Closure) {
            $handler();
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $class = 'App\\Controllers\\' . $class;

            if (!class_exists($class)) {
                throw new RuntimeException('No existe el controlador ' . $class);
            }

            $controller = new $class();
            $controller->{$method}();
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (is_string($class) && class_exists($class)) {
                $controller = new $class();
                $controller->{$method}();
                return;
            }
        }

        throw new RuntimeException('Manejador de ruta no válido.');
    }

    private static function normalize(string $path): string
    {
        $path = trim($path);
        $path = trim($path, '/');

        return $path === '' ? '' : preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $path);
    }
}
