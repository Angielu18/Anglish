<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Renderizado de vistas PHP planas.
 *
 * No se usa un motor de plantillas: PHP ya es el motor. La vista se
 * ejecuta dentro de un buffer para capturar su HTML y luego inyectarlo
 * en el layout correspondiente.
 */
final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $layout = 'app'): void
    {
        $view = str_replace(['..', '\\'], ['', '/'], $view);
        $file = APP_PATH . '/views/' . $view . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('No existe la vista: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = (string) ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layout = str_replace(['..', '\\'], ['', '/'], $layout);
        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';

        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }
}
