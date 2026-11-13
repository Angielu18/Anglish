<?php
/**
 * Vista común de errores HTTP.
 * Variables: $status, $message, $debug, $exception.
 */
$status = (int) ($status ?? 500);
$iconos = [
    403 => 'bi-shield-lock',
    404 => 'bi-compass',
    405 => 'bi-signpost-split',
    419 => 'bi-hourglass-bottom',
    500 => 'bi-tools',
];
$icono = $iconos[$status] ?? 'bi-exclamation-triangle';
$volver = \App\Core\Auth::check() ? url('dashboard') : url('login');
$textoVolver = \App\Core\Auth::check() ? 'Volver a mi panel' : 'Ir al inicio de sesión';
?>
<section class="error-screen">
    <div class="error-card">
        <span class="error-code"><?= e((string) $status) ?></span>
        <span class="error-icon"><i class="bi <?= e($icono) ?>" aria-hidden="true"></i></span>
        <h2><?= $status === 404 ? 'No encontramos esta página' : ($status === 403 ? 'Acceso restringido' : 'Algo no salió como esperábamos') ?></h2>
        <p><?= e((string) $message) ?></p>

        <?php if (!empty($debug) && isset($exception) && $exception instanceof Throwable): ?>
            <details class="error-debug">
                <summary>Detalle técnico (solo en desarrollo)</summary>
                <code><?= e(get_class($exception)) ?></code>
                <code><?= e($exception->getFile()) ?>:<?= e((string) $exception->getLine()) ?></code>
            </details>
        <?php endif; ?>

        <div class="error-actions">
            <a href="<?= e($volver) ?>" class="btn btn-primary">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> <?= e($textoVolver) ?>
            </a>
            <a href="<?= e(url('login')) ?>" class="btn btn-soft">Iniciar sesión</a>
        </div>
    </div>
</section>
