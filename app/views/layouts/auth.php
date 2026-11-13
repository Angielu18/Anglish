<?php
/**
 * Layout para pantallas sin sesión (login y errores).
 * Variables disponibles: $content, $title.
 */
$appName = (string) config('app.name', 'Anglish');
$pageTitle = isset($title) ? (string) $title : 'Acceso';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1f3a">
    <meta name="description" content="Acceso al sistema de gestión académica Anglish.">
    <title><?= e($pageTitle) ?> · <?= e($appName) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/logo-mark.svg')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=2">
</head>
<body class="auth-body">
    <div class="auth-glow auth-glow-one" aria-hidden="true"></div>
    <div class="auth-glow auth-glow-two" aria-hidden="true"></div>

    <?= $content ?>

    <script src="<?= e(asset('vendor/bootstrap/bootstrap.bundle.min.js')) ?>" defer></script>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
