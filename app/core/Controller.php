<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base de los controladores.
 *
 * Centraliza el renderizado y evita repetir la ruta de las vistas en cada
 * acción. Los controladores solo orquestan: validan, llaman al modelo y
 * eligen la vista.
 */
abstract class Controller
{
    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = [], string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }
}
