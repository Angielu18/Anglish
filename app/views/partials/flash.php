<?php
/**
 * Mensajes de una sola vez. Se consumen al pintar la vista.
 */
$mensajes = \App\Core\Flash::consume();
$iconos = [
    'success' => 'bi-check-circle-fill',
    'danger'  => 'bi-exclamation-octagon-fill',
    'warning' => 'bi-exclamation-triangle-fill',
    'info'    => 'bi-info-circle-fill',
];
?>
<?php foreach ($mensajes as $tipo => $lista): ?>
    <?php if (!is_array($lista)) { continue; } ?>
    <?php foreach ($lista as $mensaje): ?>
        <div class="alert alert-<?= e($tipo) ?> alert-dismissible fade show app-alert" role="alert">
            <i class="bi <?= e($iconos[$tipo] ?? 'bi-info-circle-fill') ?>" aria-hidden="true"></i>
            <span><?= e($mensaje) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
