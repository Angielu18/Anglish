<?php
/**
 * Layout principal de la aplicación autenticada.
 *
 * Variables disponibles: $content, $title, $subtitle, $user, $role.
 */
$appName = (string) config('app.name', 'Anglish');
$pageTitle = isset($title) ? (string) $title : 'Panel';
$roleName = isset($role) ? (string) $role : (string) ($user['rol'] ?? 'ESTUDIANTE');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1f3a">
    <meta name="description" content="Anglish, academia de inglés. Sistema de gestión académica.">
    <title><?= e($pageTitle) ?> · <?= e($appName) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/logo-mark.svg')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=2">
</head>
<body class="app-body role-<?= e(strtolower($roleName)) ?>">
    <div class="app-shell">
        <?php require APP_PATH . '/views/partials/sidebar.php'; ?>

        <div class="app-main">
            <?php require APP_PATH . '/views/partials/topbar.php'; ?>

            <main class="app-content" id="main-content">
                <?php require APP_PATH . '/views/partials/flash.php'; ?>
                <?= $content ?>
            </main>

            <footer class="app-footer">
                <span>Anglish · Academia de inglés</span>
                <span>Fase 3 · MVC + PDO</span>
            </footer>
        </div>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

    <script src="<?= e(asset('vendor/bootstrap/bootstrap.bundle.min.js')) ?>" defer></script>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
