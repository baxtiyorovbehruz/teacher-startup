<?php
$currentUser = current_user();
$successMessage = flash('success');
$errorMessage = flash('error');
$page = $page ?? 'home';
$pageTitle = $pageTitle ?? ($config['app_name'] ?? 'Tadbirkor.uz');
$filters = $filters ?? [];
?>
<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#16212b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Tadbirkor">
    <title><?= e($pageTitle) ?> | <?= e($config['app_name']) ?></title>
    <meta name="description" content="Yoshlar biznes g'oyalari uchun tadbirkorlar, mentorlar va investorlar tarmog'i.">
    <link rel="manifest" href="<?= e(asset('manifest.webmanifest')) ?>">
    <link rel="icon" href="<?= e(asset('assets/icons/icon.svg')) ?>" type="image/svg+xml" sizes="any">
    <link rel="icon" href="<?= e(asset('assets/icons/icon-192.png')) ?>" type="image/png" sizes="192x192">
    <link rel="shortcut icon" href="<?= e(asset('assets/icons/icon.svg')) ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= e(asset('assets/icons/icon-180.png')) ?>" sizes="180x180">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="<?= e($pageClass ?? '') ?>" data-csrf="<?= e(csrf_token()) ?>" data-db-ready="<?= $dbError === null ? '1' : '0' ?>">
    <div class="site-backdrop" aria-hidden="true"></div>
    <div class="noise-layer" aria-hidden="true"></div>

    <header class="topbar">
        <div class="topbar__inner">
            <a class="brand" href="/?page=home">
                <span class="brand__mark">
                    <span class="brand__pulse"></span>
                </span>
                <span class="brand__text">
                    <strong>Tadbirkor.uz</strong>
                    <small>Tadbirkorlar hamjamiyati platformasi</small>
                </span>
            </a>

            <nav class="topbar__nav">
                <div class="topbar__nav-group">
                    <a href="/?page=home" class="<?= $page === 'home' ? 'is-active' : '' ?>">Lenta</a>
                    <a href="/?page=network" class="<?= $page === 'network' ? 'is-active' : '' ?>">Tarmoq</a>
                    <a href="/?page=create" class="<?= $page === 'create' ? 'is-active' : '' ?>">G'oya qo'shish</a>
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <a href="/?page=admin" class="<?= $page === 'admin' ? 'is-active' : '' ?>">Admin</a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="topbar__auth">
                <?php if ($currentUser): ?>
                    <a class="ghost-chip <?= $page === 'profile' ? 'is-active' : '' ?>" href="/?page=profile&id=<?= (int) $currentUser['id'] ?>">
                        <?= e($currentUser['name']) ?>
                    </a>
                    <a class="btn btn--ghost" href="/?page=logout">Chiqish</a>
                <?php else: ?>
                    <a class="btn btn--ghost" href="/?page=login">Kirish</a>
                    <a class="btn btn--primary" href="/?page=register">Boshlash</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($dbError !== null): ?>
        <section class="setup-alert">
            <strong>MySQL ulanishi tayyor emas.</strong>
            <span><?= e($dbError) ?></span>
            <span>`database/schema.sql` va `database/seed.sql` fayllarini import qiling.</span>
        </section>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <section class="flash flash--success"><?= e($successMessage) ?></section>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <section class="flash flash--error"><?= e($errorMessage) ?></section>
    <?php endif; ?>

    <main class="page-shell">
        <?php require $viewFile; ?>
    </main>

    <div id="toast-stack" class="toast-stack" aria-live="polite" aria-atomic="true"></div>

    <?php if (!in_array($page, ['login', 'register'], true)): ?>
        <nav class="bottom-nav" aria-label="Mobil navigatsiya">
            <a href="/?page=home" class="<?= $page === 'home' ? 'is-active' : '' ?>">Lenta</a>
            <a href="/?page=home&sort=trend" class="<?= (!empty($filters['sort']) && $filters['sort'] === 'trend') ? 'is-active' : '' ?>">Trend</a>
            <a href="/?page=network" class="<?= $page === 'network' ? 'is-active' : '' ?>">Tarmoq</a>
            <a href="/?page=create" class="<?= $page === 'create' ? 'is-active' : '' ?>">+ G'oya</a>
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <a href="/?page=admin" class="<?= $page === 'admin' ? 'is-active' : '' ?>">Admin</a>
            <?php endif; ?>
            <?php if ($currentUser): ?>
                <a href="/?page=profile&id=<?= (int) $currentUser['id'] ?>" class="<?= $page === 'profile' ? 'is-active' : '' ?>">Profil</a>
            <?php else: ?>
                <a href="/?page=login" class="<?= $page === 'login' ? 'is-active' : '' ?>">Kirish</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <script>
        window.APP_CONFIG = {
            csrfToken: <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            baseUrl: <?= json_encode(base_url(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        };
    </script>
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
